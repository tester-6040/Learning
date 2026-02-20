<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\ApiResponse;
use App\Models\Attendance;
use App\Services\AttendanceService;
use InvalidArgumentException;

final class AttendanceController
{
    public function __construct(
        private readonly AttendanceService $service,
        private readonly Attendance $attendance
    ) {
    }

    public function store(array $payload): void
    {
        try {
            $this->service->mark($payload);
            ApiResponse::json(['message' => 'Attendance saved']);
        } catch (InvalidArgumentException $exception) {
            ApiResponse::json(['error' => $exception->getMessage()], 422);
        }
    }

    public function summary(string $month): void
    {
        ApiResponse::json(['data' => $this->attendance->monthlySummary($month)]);
    }

    public function exportCsv(string $month): void
    {
        $rows = $this->attendance->monthlySummary($month);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_report_' . $month . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Employee Code', 'Name', 'Department', 'Present', 'Half Day', 'Leave', '1 Hour Permission', 'Attendance %']);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['employee_code'],
                $row['full_name'],
                $row['department'],
                $row['present_days'],
                $row['half_days'],
                $row['leave_days'],
                $row['one_hour_permissions'],
                $row['attendance_percentage'] ?? 0,
            ]);
        }

        fclose($output);
    }
}
