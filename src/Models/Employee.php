<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class Employee extends BaseModel
{
    public function all(): array
    {
        return $this->select('SELECT * FROM employees ORDER BY id DESC');
    }

    public function find(int $id): ?array
    {
        return $this->selectOne('SELECT * FROM employees WHERE id = :id', ['id' => $id]);
    }

    public function create(array $data): int
    {
        return $this->insert(
            'INSERT INTO employees (employee_code, full_name, email, department, joining_date) VALUES (:employee_code, :full_name, :email, :department, :joining_date)',
            $data
        );
    }

    public function updateEmployee(int $id, array $data): bool
    {
        $data['id'] = $id;
        return $this->execute(
            'UPDATE employees SET employee_code = :employee_code, full_name = :full_name, email = :email, department = :department, joining_date = :joining_date WHERE id = :id',
            $data
        );
    }
}
