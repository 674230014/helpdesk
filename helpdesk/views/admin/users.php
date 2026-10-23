<?php use App\Enums\UserRole; ?>
<h1 class="text-xl font-bold mb-4">จัดการผู้ใช้</h1>
<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white border rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left text-slate-600">
            <tr><th class="px-3 py-2">ชื่อ</th><th class="px-3 py-2">อีเมล</th><th class="px-3 py-2">บทบาท</th><th class="px-3 py-2">LINE ID</th></tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr class="border-t">
                    <td class="px-3 py-2"><?= e($u['name']) ?></td>
                    <td class="px-3 py-2"><?= e($u['email']) ?></td>
                    <td class="px-3 py-2"><?= e(UserRole::from($u['role'])->label()) ?></td>
                    <td class="px-3 py-2 text-slate-500"><?= e($u['line_user_id'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <form class="json-form bg-white border rounded-lg p-5 space-y-3 h-fit" action="/admin/users">
        <h2 class="font-semibold">เพิ่มผู้ใช้</h2>
        <input name="name" placeholder="ชื่อ" required class="w-full border rounded px-3 py-2 text-sm">
        <input name="email" type="email" placeholder="อีเมล" required class="w-full border rounded px-3 py-2 text-sm">
        <input name="password" type="password" minlength="8" placeholder="รหัสผ่าน (≥ 8 ตัว)" required class="w-full border rounded px-3 py-2 text-sm">
        <select name="role" class="w-full border rounded px-2 py-2 text-sm">
            <?php foreach (UserRole::cases() as $r): ?>
                <option value="<?= $r->value ?>"><?= e($r->label()) ?></option>
            <?php endforeach; ?>
        </select>
        <input name="line_user_id" placeholder="LINE userId (ไม่บังคับ)" class="w-full border rounded px-3 py-2 text-sm">
        <button class="w-full bg-indigo-600 text-white rounded py-2 text-sm">บันทึก</button>
    </form>
</div>
