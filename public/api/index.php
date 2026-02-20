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
$path = trim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$script = trim((string) ($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$route = trim((string) ($_GET['route'] ?? ''), '/');

if ($route === '') {
    if (str_ends_with($path, $script)) {
        $route = trim(substr($path, 0, -strlen($script)), '/');
    } elseif (preg_match('#(^|/)api/(.*)$#', $path, $matches) === 1) {
        $route = trim($matches[2], '/');
    }
}

$payload = ApiResponse::input();

if ($route === 'employees' && $method === 'GET') {
    $employeeController->index();
    return;
}

if ($route === 'employees' && $method === 'POST') {
    $employeeController->store($payload ?: $_POST);
    return;
}

if (preg_match('#^employees/(\d+)$#', $route, $matches) === 1 && in_array($method, ['PUT', 'PATCH'], true)) {
    $employeeController->update((int) $matches[1], $payload);
    return;
}

if ($route === 'attendance' && $method === 'POST') {
    $attendanceController->store($payload ?: $_POST);
    return;
}

if ($route === 'attendance/summary' && $method === 'GET') {
    $month = $_GET['month'] ?? date('Y-m');
    $attendanceController->summary($month);
    return;
}

if ($route === 'attendance/export' && $method === 'GET') {
    $month = $_GET['month'] ?? date('Y-m');
    $attendanceController->exportCsv($month);
    return;
}

ApiResponse::json([
    'error' => 'Route not found',
    'hint' => 'Use ?route=employees, ?route=attendance, ?route=attendance/summary, or ?route=attendance/export',
], 404);
