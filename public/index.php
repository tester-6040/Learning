<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Models\Attendance;
use App\Models\Employee;

$employeeModel = new Employee();
$attendanceModel = new Attendance();

$month = $_GET['month'] ?? date('Y-m');
$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = $basePath === '' ? '' : $basePath;
$apiBase = $basePath . '/api/index.php';
$employees = $employeeModel->all();
$summary = $attendanceModel->monthlySummary($month);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Attendance Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800">
    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <header class="bg-white shadow rounded-xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">Office Attendance Pro</h1>
                <p class="text-slate-500">Professional attendance and reporting platform for 100+ employees.</p>
            </div>
            <form method="GET" class="flex gap-2 items-end">
                <label class="text-sm font-medium">Month
                    <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" class="mt-1 border rounded-lg px-3 py-2">
                </label>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-lg">Filter</button>
                <a class="bg-emerald-600 text-white px-4 py-2 rounded-lg" href="<?= htmlspecialchars($apiBase) ?>?route=attendance/export&amp;month=<?= htmlspecialchars($month) ?>">Export CSV</a>
            </form>
        </header>

        <section class="grid md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-lg mb-4">Add Employee</h2>
                <form id="employeeForm" class="grid grid-cols-2 gap-3">
                    <input required name="employee_code" class="border rounded-lg p-2" placeholder="Employee Code">
                    <input required name="full_name" class="border rounded-lg p-2" placeholder="Full Name">
                    <input required type="email" name="email" class="border rounded-lg p-2" placeholder="Email">
                    <input required name="department" class="border rounded-lg p-2" placeholder="Department">
                    <input required type="date" name="joining_date" class="border rounded-lg p-2 col-span-2">
                    <button class="bg-blue-600 text-white rounded-lg py-2 col-span-2">Save Employee</button>
                </form>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="font-semibold text-lg mb-4">Mark Attendance</h2>
                <p class="text-xs text-amber-600 mb-2">1 hour permission is allowed once per employee per month with no loss of pay.</p>
                <form id="attendanceForm" class="grid grid-cols-2 gap-3">
                    <select name="employee_id" required class="border rounded-lg p-2 col-span-2">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"><?= htmlspecialchars($employee['employee_code'] . ' - ' . $employee['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" required name="attendance_date" class="border rounded-lg p-2">
                    <select name="status" required class="border rounded-lg p-2">
                        <option value="present">Present</option>
                        <option value="half_day">Half Day</option>
                        <option value="leave">Leave</option>
                        <option value="permission_1h">1 Hour Permission</option>
                    </select>
                    <textarea name="remarks" placeholder="Remarks" class="border rounded-lg p-2 col-span-2"></textarea>
                    <button class="bg-emerald-600 text-white rounded-lg py-2 col-span-2">Save Attendance</button>
                </form>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow p-6 overflow-x-auto">
            <h2 class="font-semibold text-lg mb-4">Monthly Attendance Dashboard</h2>
            <table class="w-full text-sm">
                <thead>
                <tr class="text-left border-b bg-slate-50">
                    <th class="p-2">Code</th>
                    <th class="p-2">Name</th>
                    <th class="p-2">Department</th>
                    <th class="p-2">Present</th>
                    <th class="p-2">Half Day</th>
                    <th class="p-2">Leave</th>
                    <th class="p-2">1H Permission</th>
                    <th class="p-2">Attendance %</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($summary as $row): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-2"><?= htmlspecialchars($row['employee_code']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['department']) ?></td>
                        <td class="p-2"><?= (int) $row['present_days'] ?></td>
                        <td class="p-2"><?= (int) $row['half_days'] ?></td>
                        <td class="p-2"><?= (int) $row['leave_days'] ?></td>
                        <td class="p-2"><?= (int) $row['one_hour_permissions'] ?></td>
                        <td class="p-2 font-semibold"><?= $row['attendance_percentage'] ?? '0.00' ?>%</td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>

<script>
const API_BASE = <?= json_encode($apiBase, JSON_UNESCAPED_SLASHES) ?>;

async function submitForm(formId, endpoint) {
    const form = document.getElementById(formId);
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const payload = Object.fromEntries(new FormData(form).entries());

        const response = await fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const contentType = response.headers.get('content-type') || '';
        const result = contentType.includes('application/json')
            ? await response.json()
            : { error: `Unexpected response from server (HTTP ${response.status}). Please verify API route setup.` };

        alert(result.message || result.error || 'Action complete');
        if (response.ok) {
            window.location.reload();
        }
    });
}

submitForm('employeeForm', `${API_BASE}?route=employees`);
submitForm('attendanceForm', `${API_BASE}?route=attendance`);
</script>
</body>
</html>
