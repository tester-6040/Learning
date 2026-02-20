<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class User extends BaseModel
{
    public function findByUsername(string $username): ?array
    {
        return $this->selectOne('SELECT id, username, password_hash, full_name FROM users WHERE username = :username LIMIT 1', [
            'username' => $username,
        ]);
    }

    public function ensureDefaultAdmin(): void
    {
        $existing = $this->findByUsername('admin');
        if ($existing !== null) {
            return;
        }

        $this->insert(
            'INSERT INTO users (username, password_hash, full_name) VALUES (:username, :password_hash, :full_name)',
            [
                'username' => 'admin',
                'password_hash' => password_hash('admin@123', PASSWORD_DEFAULT),
                'full_name' => 'System Administrator',
            ]
        );
    }
}
