<?php

namespace App\Controllers;

use App\Core\DB;
use App\Core\Security;
use App\Core\View;

final class AuthController extends BaseController
{
    public function home(): void
    {
        $user = Security::user();

        if (!$user) {
            Security::redirect('/login');
        }

        Security::redirect('/' . $user['role']);
    }

    public function loginPage(): void
    {
        if (Security::user()) {
            Security::redirect('/');
        }

        View::render('auth/login', ['title' => 'Sign in']);
    }

    public function login(): void
    {
        $this->requirePost();
        $email = strtolower(\input('email', 191));
        $password = (string) ($_POST['password'] ?? '');

        $user = DB::one(
            'SELECT id, name, email, password, role, is_active FROM users WHERE email = ?',
            [$email]
        );

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password'])) {
            Security::redirect('/login', 'Invalid email or password.');
        }

        if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
            DB::exec(
                'UPDATE users SET password = ? WHERE id = ?',
                [password_hash($password, PASSWORD_DEFAULT), $user['id']]
            );
        }

        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        Security::redirect('/');
    }

    public function logout(): void
    {
        $this->requirePost();
        Security::logout();
        Security::redirect('/login');
    }
}
