<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Response;
use App\Core\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            Response::redirect('/tickets');
        }
        View::render('auth/login', ['error' => $_SESSION['_flash_error'] ?? null], 'layouts/guest');
        unset($_SESSION['_flash_error']);
    }

    public function login(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (Auth::login($email, $password)) {
            Response::redirect(Auth::role()?->value === 'admin' ? '/admin/dashboard' : '/tickets');
        }
        $_SESSION['_flash_error'] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
        Response::redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        Response::redirect('/login');
    }
}
