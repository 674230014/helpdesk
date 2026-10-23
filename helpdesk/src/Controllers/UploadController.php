<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Response;
use App\Enums\UserRole;

/** เสิร์ฟรูปจาก storage/ (นอก DocumentRoot) เฉพาะผู้มีสิทธิ์ดู ticket นั้น */
final class UploadController
{
    public function __construct(private Database $db)
    {
    }

    public function show(string $file): void
    {
        if (!preg_match('/^[a-f0-9]{32}\.(jpg|png|webp|gif)$/', $file)) {
            Response::abort(404);
        }
        $row = $this->db->query(
            'SELECT t.user_id, t.technician_id FROM comments c JOIN tickets t ON t.id = c.ticket_id WHERE c.image_path = ?',
            [$file]
        )->fetch();
        $role = Auth::role();
        if (!$row || !($role === UserRole::Admin || (int) $row['user_id'] === Auth::id() || (int) $row['technician_id'] === Auth::id())) {
            Response::abort(403);
        }
        $path = BASE_PATH . '/storage/uploads/ticket-images/' . $file;
        if (!is_file($path)) {
            Response::abort(404);
        }
        header('Content-Type: ' . (new \finfo(FILEINFO_MIME_TYPE))->file($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=3600');
        readfile($path);
        exit;
    }
}
