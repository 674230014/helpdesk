<?php
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
?>
<div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-bold">รายการ Ticket</h1>
</div>

<form method="get" class="flex flex-wrap gap-2 mb-4">
    <input name="q" value="<?= e($q) ?>" placeholder="ค้นหาหัวข้อ/รายละเอียด" class="border rounded px-3 py-2 text-sm w-64">
    <select name="status" class="border rounded px-2 py-2 text-sm">
        <option value="">ทุกสถานะ</option>
        <?php foreach (TicketStatus::cases() as $s): ?>
            <option value="<?= $s->value ?>" <?= ($filters['status'] ?? '') === $s->value ? 'selected' : '' ?>><?= e($s->label()) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="priority" class="border rounded px-2 py-2 text-sm">
        <option value="">ทุกความสำคัญ</option>
        <?php foreach (TicketPriority::cases() as $p): ?>
            <option value="<?= $p->value ?>" <?= ($filters['priority'] ?? '') === $p->value ? 'selected' : '' ?>><?= e($p->label()) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="px-4 py-2 text-sm bg-slate-800 text-white rounded">ค้นหา</button>
</form>

<div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left text-slate-600">
        <tr>
            <th class="px-3 py-2">#</th><th class="px-3 py-2">หัวข้อ</th><th class="px-3 py-2">หมวด</th>
            <th class="px-3 py-2">ผู้แจ้ง</th><th class="px-3 py-2">ช่าง</th>
            <th class="px-3 py-2">ความสำคัญ</th><th class="px-3 py-2">สถานะ</th><th class="px-3 py-2">สร้างเมื่อ</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t):
            $st = TicketStatus::from($t['status']);
            $pr = TicketPriority::from($t['priority']); ?>
            <tr class="border-t hover:bg-slate-50">
                <td class="px-3 py-2 text-slate-500"><?= (int) $t['id'] ?></td>
                <td class="px-3 py-2"><a class="text-indigo-700 hover:underline" href="/tickets/<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a></td>
                <td class="px-3 py-2"><?= e($t['category_name']) ?></td>
                <td class="px-3 py-2"><?= e($t['requester_name']) ?></td>
                <td class="px-3 py-2"><?= e($t['technician_name'] ?? '-') ?></td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs <?= $pr->badge() ?>"><?= e($pr->label()) ?></span></td>
                <td class="px-3 py-2"><span class="px-2 py-0.5 rounded-full text-xs <?= $st->badge() ?>"><?= e($st->label()) ?></span></td>
                <td class="px-3 py-2 text-slate-500"><?= e(thai_date($t['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$tickets): ?>
            <tr><td colspan="8" class="px-3 py-10 text-center text-slate-400">ไม่พบ Ticket</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
