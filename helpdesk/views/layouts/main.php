<?php
use App\Core\Auth;
use App\Core\Csrf;
$u = Auth::user();
$role = $u['role'] ?? '';
$roleLabel = $role ? \App\Enums\UserRole::from($role)->label() : '';
?><!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title>Helpdesk Ticket System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
<nav class="bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 h-14 flex items-center gap-6">
        <a href="/tickets" class="font-bold text-indigo-700">🛠 Helpdesk</a>
        <a href="/tickets" class="text-sm hover:text-indigo-700">Ticket ทั้งหมด</a>
        <?php if (in_array($role, ['user', 'admin'], true)): ?>
            <a href="/tickets/create" class="text-sm hover:text-indigo-700">แจ้งปัญหาใหม่</a>
        <?php endif; ?>
        <?php if ($role === 'admin'): ?>
            <a href="/admin/dashboard" class="text-sm hover:text-indigo-700">Dashboard</a>
            <a href="/admin/users" class="text-sm hover:text-indigo-700">ผู้ใช้</a>
            <a href="/admin/categories" class="text-sm hover:text-indigo-700">หมวดหมู่</a>
        <?php endif; ?>
        <div class="ml-auto flex items-center gap-3 text-sm">
            <span class="text-slate-500"><?= e($u['name'] ?? '') ?> · <?= e($roleLabel) ?></span>
            <form method="post" action="/logout">
                <input type="hidden" name="_csrf" value="<?= e(Csrf::token()) ?>">
                <button class="px-3 py-1 rounded border border-slate-300 hover:bg-slate-100">ออกจากระบบ</button>
            </form>
        </div>
    </div>
</nav>
<main class="max-w-6xl mx-auto px-4 py-6"><?= $content ?></main>
<div id="toast" class="fixed bottom-4 right-4 hidden px-4 py-3 rounded-lg shadow-lg text-white text-sm"></div>
<script src="/assets/app.js"></script>
</body>
</html>
