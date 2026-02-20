<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Models\Employee;

final class EmployeeController
{
    public function __construct(private readonly Employee $employee)
    {
    }

    public function index(): void
    {
        ApiResponse::json(['data' => $this->employee->all()]);
    }

    public function store(array $payload): void
    {
        $required = ['employee_code', 'full_name', 'email', 'department', 'joining_date'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                ApiResponse::json(['error' => "$field is required"], 422);
                return;
            }
        }

        $id = $this->employee->create($payload);
        ApiResponse::json(['message' => 'Employee created', 'id' => $id], 201);
    }

    public function update(int $id, array $payload): void
    {
        $employee = $this->employee->find($id);
        if ($employee === null) {
            ApiResponse::json(['error' => 'Employee not found'], 404);
            return;
        }

        $this->employee->updateEmployee($id, [
            'employee_code' => $payload['employee_code'] ?? $employee['employee_code'],
            'full_name' => $payload['full_name'] ?? $employee['full_name'],
            'email' => $payload['email'] ?? $employee['email'],
            'department' => $payload['department'] ?? $employee['department'],
            'joining_date' => $payload['joining_date'] ?? $employee['joining_date'],
        ]);

        ApiResponse::json(['message' => 'Employee updated']);
    }
}
