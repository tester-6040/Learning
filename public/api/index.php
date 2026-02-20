<?php

declare(strict_types=1);

use App\Controllers\AttendanceController;
use App\Controllers\EmployeeController;
use App\Core\ApiResponse;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AttendanceService;

require dirname(__DIR__, 2) . '/bootstrap.php';

$employeeController = new EmployeeController(new Employee());
$attendanceModel = new Attendance();
$attendanceController = new AttendanceController(new AttendanceService($attendanceModel), $attendanceModel);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$payload = ApiResponse::input();

if ($path === '/api/employees' && $method === 'GET') {
    $employeeController->index();
    return;
}

if ($path === '/api/employees' && $method === 'POST') {
    $employeeController->store($payload ?: $_POST);
    return;
}

if (preg_match('#^/api/employees/(\d+)$#', $path, $matches) && in_array($method, ['PUT', 'PATCH'], true)) {
    $employeeController->update((int) $matches[1], $payload);
    return;
}

if ($path === '/api/attendance' && $method === 'POST') {
    $attendanceController->store($payload ?: $_POST);
    return;
}

if ($path === '/api/attendance/summary' && $method === 'GET') {
    $month = $_GET['month'] ?? date('Y-m');
    $attendanceController->summary($month);
    return;
}

if ($path === '/api/attendance/export' && $method === 'GET') {
    $month = $_GET['month'] ?? date('Y-m');
    $attendanceController->exportCsv($month);
    return;
}

ApiResponse::json(['error' => 'Route not found'], 404);
