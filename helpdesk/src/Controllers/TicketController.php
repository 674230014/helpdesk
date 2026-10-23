<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\Validator;
use App\Core\View;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Repositories\CategoryRepository;
use App\Repositories\CommentRepository;
use App\Repositories\RatingRepository;
use App\Repositories\StatusLogRepository;
use App\Repositories\TicketRepository;
use App\Repositories\UserRepository;
use App\Services\TicketService;
use App\Services\TicketStatusService;

final class TicketController
{
    public function __construct(
        private TicketStatusService $statusService,
        private TicketService $ticketService,
        private TicketRepository $repo,
        private CategoryRepository $categories,
        private CommentRepository $comments,
        private StatusLogRepository $logs,
        private RatingRepository $ratings,
        private UserRepository $users
    ) {
    }

    public function index(): void
    {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
        ];
        $role = Auth::role();
        if ($role === UserRole::User) {
            $filters['user_id'] = Auth::id();
        } elseif ($role === UserRole::Technician) {
            $filters['technician_id'] = Auth::id();
        }
        $keyword = trim((string) ($_GET['q'] ?? ''));
        View::render('tickets/index', [
            'tickets' => $this->repo->search($keyword, $filters),
            'q' => $keyword,
            'filters' => $filters,
        ]);
    }

    public function create(): void
    {
        View::render('tickets/create', ['categories' => $this->categories->all()]);
    }

    /** POST /tickets — Sequence 6.1 */
    public function store(): void
    {
        $v = new Validator();
        $priorities = implode(',', array_column(TicketPriority::cases(), 'value'));
        if (!$v->validate($_POST, [
            'title' => 'required|max:150',
            'description' => 'required|max:5000',
            'category_id' => 'required|int',
            'priority' => "required|in:$priorities",
        ]) || !$this->categories->find((int) $_POST['category_id'])) {
            Response::json(['ok' => false, 'error' => 'ข้อมูลไม่ถูกต้อง', 'errors' => $v->errors()], 422);
        }
        try {
            $ticket = $this->ticketService->createTicket($_POST, $_FILES['image'] ?? null, Auth::id());
        } catch (\RuntimeException $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        Response::json(['ok' => true, 'id' => (int) $ticket['id'], 'redirect' => '/tickets/' . $ticket['id']], 201);
    }

    public function show(string $id): void
    {
        $ticket = $this->loadAuthorized((int) $id);
        $status = TicketStatus::from($ticket['status']);
        $user = Auth::user();
        $role = Auth::role();
        $isOwner = (int) $ticket['user_id'] === Auth::id();
        $isTech = $role === UserRole::Technician && (int) $ticket['technician_id'] === Auth::id();

        // กำหนด action panel ที่ role นี้ทำได้ ณ สถานะปัจจุบัน
        $action = match (true) {
            $role === UserRole::Admin && $status === TicketStatus::Open => 'assign',
            $isTech && $status === TicketStatus::Assigned => 'accept',
            $isTech && $status === TicketStatus::InProgress => 'resolve',
            $isOwner && $status === TicketStatus::Resolved => 'confirm',
            default => null,
        };

        View::render('tickets/show', [
            'ticket' => $ticket,
            'status' => $status,
            'priority' => TicketPriority::from($ticket['priority']),
            'comments' => $this->comments->forTicket((int) $id),
            'logs' => $this->logs->forTicket((int) $id),
            'rating' => $this->ratings->findByTicket((int) $id),
            'technicians' => $action === 'assign' ? $this->users->byRole('technician') : [],
            'action' => $action,
            'canComment' => $status !== TicketStatus::Closed,
            'me' => $user,
        ]);
    }

    /** PATCH /api/tickets/{id}/status (ส่งเป็น POST + X-HTTP-Method-Override) */
    public function updateStatus(string $id): void
    {
        $ticket = $this->loadAuthorized((int) $id);
        $to = TicketStatus::tryFrom((string) ($_POST['status'] ?? ''));
        if (!$to) {
            Response::json(['ok' => false, 'error' => 'สถานะไม่ถูกต้อง'], 422);
        }
        try {
            $fresh = $this->statusService->transition($ticket, $to, Auth::user(), $_POST, $_FILES['image'] ?? null);
        } catch (\DomainException $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], $e->getCode() === 403 ? 403 : 422);
        } catch (\RuntimeException $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        Response::json(['ok' => true, 'status' => $fresh['status'], 'label' => TicketStatus::from($fresh['status'])->label()]);
    }

    public function addComment(string $id): void
    {
        $ticket = $this->loadAuthorized((int) $id);
        $body = trim((string) ($_POST['body'] ?? ''));
        if ($body === '' || mb_strlen($body) > 3000) {
            Response::json(['ok' => false, 'error' => 'กรุณาพิมพ์ข้อความ (ไม่เกิน 3000 ตัวอักษร)'], 422);
        }
        if ($ticket['status'] === TicketStatus::Closed->value) {
            Response::json(['ok' => false, 'error' => 'Ticket ปิดแล้ว ไม่สามารถแสดงความเห็นได้'], 422);
        }
        try {
            $this->ticketService->addComment((int) $id, Auth::id(), $body, $_FILES['image'] ?? null);
        } catch (\RuntimeException $e) {
            Response::json(['ok' => false, 'error' => $e->getMessage()], 422);
        }
        Response::json(['ok' => true], 201);
    }

    /** ตรวจสิทธิ์ดู ticket: เจ้าของ / ช่างที่ได้รับมอบหมาย / admin */
    private function loadAuthorized(int $id): array
    {
        $ticket = $this->repo->findDetailed($id);
        if (!$ticket) {
            Response::abort(404, 'ไม่พบ Ticket');
        }
        $role = Auth::role();
        $allowed = $role === UserRole::Admin
            || (int) $ticket['user_id'] === Auth::id()
            || ($role === UserRole::Technician && (int) $ticket['technician_id'] === Auth::id());
        if (!$allowed) {
            Response::abort(403, 'คุณไม่มีสิทธิ์เข้าถึง Ticket นี้');
        }
        return $ticket;
    }
}
