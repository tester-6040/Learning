<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\SessionManager;
use App\Models\User;

final class AuthService
{
    private const SESSION_KEY = 'auth_user';

    public function __construct(private readonly User $users)
    {
    }

    public function bootDefaultAdmin(): void
    {
        $this->users->ensureDefaultAdmin();
    }

    public function login(string $username, string $password): bool
    {
        $user = $this->users->findByUsername($username);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        SessionManager::set(self::SESSION_KEY, [
            'id' => (int) $user['id'],
            'username' => $user['username'],
            'full_name' => $user['full_name'],
        ]);

        return true;
    }

    public function logout(): void
    {
        SessionManager::remove(self::SESSION_KEY);
    }

    public function user(): ?array
    {
        $user = SessionManager::get(self::SESSION_KEY);
        return is_array($user) ? $user : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }
}
