<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Core\Validator;
use App\Core\View;
use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;

/** จัดการผู้ใช้และหมวดหมู่ (Admin only) */
final class AdminController
{
    public function __construct(private UserRepository $users, private CategoryRepository $categories)
    {
    }

    public function users(): void
    {
        View::render('admin/users', ['users' => $this->users->all()]);
    }

    public function storeUser(): void
    {
        $v = new Validator();
        if (!$v->validate($_POST, [
            'name' => 'required|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8|max:100',
            'role' => 'required|in:user,technician,admin',
            'line_user_id' => 'max:64',
        ])) {
            Response::json(['ok' => false, 'error' => implode(' / ', $v->errors())], 422);
        }
        if ($this->users->findByEmail($_POST['email'])) {
            Response::json(['ok' => false, 'error' => 'อีเมลนี้ถูกใช้แล้ว'], 422);
        }
        $this->users->create([
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role' => $_POST['role'],
            'line_user_id' => trim((string) ($_POST['line_user_id'] ?? '')) ?: null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        Response::json(['ok' => true], 201);
    }

    public function categories(): void
    {
        View::render('admin/categories', ['categories' => $this->categories->all()]);
    }

    public function storeCategory(): void
    {
        $v = new Validator();
        if (!$v->validate($_POST, ['name' => 'required|max:100', 'description' => 'max:500'])) {
            Response::json(['ok' => false, 'error' => implode(' / ', $v->errors())], 422);
        }
        $this->categories->create(['name' => trim($_POST['name']), 'description' => trim((string) ($_POST['description'] ?? ''))]);
        Response::json(['ok' => true], 201);
    }
}
