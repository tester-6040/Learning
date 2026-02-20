<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use InvalidArgumentException;

final class AttendanceService
{
    public function __construct(private readonly Attendance $attendance)
    {
    }

    public function mark(array $payload): void
    {
        $required = ['employee_id', 'attendance_date', 'status'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                throw new InvalidArgumentException("$field is required");
            }
        }

        $status = $payload['status'];
        $validStatuses = [
            Attendance::STATUS_PRESENT,
            Attendance::STATUS_HALF_DAY,
            Attendance::STATUS_LEAVE,
            Attendance::STATUS_PERMISSION_1H,
        ];

        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException('Invalid attendance status supplied');
        }

        if ($status === Attendance::STATUS_PERMISSION_1H && $this->attendance->oneHourPermissionUsedInMonth((int) $payload['employee_id'], $payload['attendance_date'])) {
            throw new InvalidArgumentException('One hour permission is allowed only once per month for each employee');
        }

        $this->attendance->mark([
            'employee_id' => (int) $payload['employee_id'],
            'attendance_date' => $payload['attendance_date'],
            'status' => $status,
            'remarks' => $payload['remarks'] ?? '',
        ]);
    }
}
