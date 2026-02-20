<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Services\AuthService;

final class AuthController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function login(array $payload): void
    {
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($username === '' || $password === '') {
            ApiResponse::json(['error' => 'username and password are required'], 422);
            return;
        }

        if (!$this->auth->login($username, $password)) {
            ApiResponse::json(['error' => 'Invalid credentials'], 401);
            return;
        }

        ApiResponse::json(['message' => 'Login successful', 'user' => $this->auth->user()]);
    }

    public function logout(): void
    {
        $this->auth->logout();
        ApiResponse::json(['message' => 'Logged out']);
    }

    public function me(): void
    {
        ApiResponse::json(['user' => $this->auth->user()]);
    }
}
