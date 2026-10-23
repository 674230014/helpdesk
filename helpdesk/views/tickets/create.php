<?php use App\Enums\TicketPriority; ?>
<h1 class="text-xl font-bold mb-4">แจ้งปัญหาใหม่</h1>
<form id="ticket-form" action="/tickets" method="post" enctype="multipart/form-data"
      class="bg-white border border-slate-200 rounded-lg p-6 space-y-4 max-w-2xl">
    <label class="block text-sm">หัวข้อ
        <input name="title" maxlength="150" required class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <div class="grid grid-cols-2 gap-4">
        <label class="block text-sm">หมวดหมู่
            <select name="category_id" required class="mt-1 w-full border rounded px-2 py-2">
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label class="block text-sm">ความสำคัญ
            <select name="priority" class="mt-1 w-full border rounded px-2 py-2">
                <?php foreach (TicketPriority::cases() as $p): ?>
                    <option value="<?= $p->value ?>" <?= $p === TicketPriority::Medium ? 'selected' : '' ?>><?= e($p->label()) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
    </div>
    <label class="block text-sm">รายละเอียดปัญหา
        <textarea name="description" rows="5" required class="mt-1 w-full border rounded px-3 py-2"></textarea>
    </label>
    <label class="block text-sm">แนบรูป (ไม่บังคับ · JPG/PNG/WEBP/GIF ≤ 5MB)
        <input type="file" name="image" accept="image/*" class="mt-1 block text-sm">
    </label>
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded px-5 py-2 text-sm">ส่ง Ticket</button>
</form>
