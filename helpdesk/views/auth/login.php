<?php use App\Core\Csrf; ?>
<form method="post" action="/login" class="bg-white w-full max-w-sm p-8 rounded-xl shadow border border-slate-200 space-y-4">
    <h1 class="text-xl font-bold text-indigo-700">🛠 Helpdesk Ticket System</h1>
    <p class="text-sm text-slate-500">เข้าสู่ระบบเพื่อแจ้งและติดตามงานซ่อม</p>
    <?php if ($error): ?>
        <div class="text-sm bg-red-50 text-red-700 border border-red-200 rounded p-2"><?= e($error) ?></div>
    <?php endif; ?>
    <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
    <label class="block text-sm">อีเมล
        <input type="email" name="email" required autofocus class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <label class="block text-sm">รหัสผ่าน
        <input type="password" name="password" required class="mt-1 w-full border rounded px-3 py-2">
    </label>
    <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded py-2">เข้าสู่ระบบ</button>
</form>
