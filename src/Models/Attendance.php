<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

final class Attendance extends BaseModel
{
    public const STATUS_PRESENT = 'present';
    public const STATUS_HALF_DAY = 'half_day';
    public const STATUS_LEAVE = 'leave';
    public const STATUS_PERMISSION_1H = 'permission_1h';

    public function mark(array $data): int
    {
        return $this->insert(
            'INSERT INTO attendances (employee_id, attendance_date, status, remarks) VALUES (:employee_id, :attendance_date, :status, :remarks)
            ON DUPLICATE KEY UPDATE status = VALUES(status), remarks = VALUES(remarks), updated_at = CURRENT_TIMESTAMP',
            $data
        );
    }

    public function oneHourPermissionUsedInMonth(int $employeeId, string $date): bool
    {
        $month = date('Y-m', strtotime($date));
        $record = $this->selectOne(
            "SELECT id FROM attendances WHERE employee_id = :employee_id AND status = :status AND DATE_FORMAT(attendance_date, '%Y-%m') = :month LIMIT 1",
            [
                'employee_id' => $employeeId,
                'status' => self::STATUS_PERMISSION_1H,
                'month' => $month,
            ]
        );

        return $record !== null;
    }

    public function monthlySummary(string $month): array
    {
        return $this->select(
            "SELECT e.id, e.employee_code, e.full_name, e.department,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) AS present_days,
                SUM(CASE WHEN a.status = 'half_day' THEN 1 ELSE 0 END) AS half_days,
                SUM(CASE WHEN a.status = 'leave' THEN 1 ELSE 0 END) AS leave_days,
                SUM(CASE WHEN a.status = 'permission_1h' THEN 1 ELSE 0 END) AS one_hour_permissions,
                COUNT(a.id) AS working_entries,
                ROUND(
                    (
                        SUM(CASE WHEN a.status = 'present' THEN 1 WHEN a.status = 'half_day' THEN 0.5 WHEN a.status = 'permission_1h' THEN 1 ELSE 0 END)
                        / NULLIF(COUNT(a.id), 0)
                    ) * 100,
                    2
                ) AS attendance_percentage
             FROM employees e
             LEFT JOIN attendances a ON a.employee_id = e.id AND DATE_FORMAT(a.attendance_date, '%Y-%m') = :month
             GROUP BY e.id, e.employee_code, e.full_name, e.department
             ORDER BY e.full_name ASC",
            ['month' => $month]
        );
    }

    public function attendanceRows(string $month): array
    {
        return $this->select(
            "SELECT a.id, e.employee_code, e.full_name, e.department, a.attendance_date, a.status, a.remarks
             FROM attendances a
             INNER JOIN employees e ON e.id = a.employee_id
             WHERE DATE_FORMAT(a.attendance_date, '%Y-%m') = :month
             ORDER BY a.attendance_date DESC, e.full_name ASC",
            ['month' => $month]
        );
    }
}
