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

$totalEmployees = count($employees);
$activeEmployees = count(array_filter($summary, static fn (array $row): bool => (int) ($row['working_entries'] ?? 0) > 0));
$avgAttendance = $activeEmployees > 0
    ? round(array_sum(array_map(static fn (array $row): float => (float) ($row['attendance_percentage'] ?? 0), $summary)) / max(count($summary), 1), 2)
    : 0;
$totalLeaves = array_sum(array_map(static fn (array $row): int => (int) ($row['leave_days'] ?? 0), $summary));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office Attendance Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="bg-slate-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-5 flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Office Attendance Pro</h1>
                <p class="text-slate-300 text-sm">Professional attendance operations for 100+ employees</p>
            </div>
            <span class="hidden md:inline-flex bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 rounded-full px-4 py-1 text-sm font-medium">
                <?= htmlspecialchars(date('F Y', strtotime($month . '-01'))) ?> Cycle
            </span>
        </div>
    </header>

    <main class="max-w-7xl mx-auto p-6 space-y-6">
        <section class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-slate-500 text-sm">Total Employees</p>
                <p class="text-2xl font-bold mt-1"><?= $totalEmployees ?></p>
            </article>
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-slate-500 text-sm">Employees with Entries</p>
                <p class="text-2xl font-bold mt-1"><?= $activeEmployees ?></p>
            </article>
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-slate-500 text-sm">Average Attendance</p>
                <p class="text-2xl font-bold mt-1"><?= number_format($avgAttendance, 2) ?>%</p>
            </article>
            <article class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <p class="text-slate-500 text-sm">Total Leave Days</p>
                <p class="text-2xl font-bold mt-1"><?= $totalLeaves ?></p>
            </article>
        </section>

        <section class="bg-white border border-slate-200 rounded-xl shadow-sm p-5">
            <form method="GET" class="flex flex-wrap gap-3 items-end">
                <label class="text-sm font-medium">Select Month
                    <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" class="mt-1 border border-slate-300 rounded-lg px-3 py-2">
                </label>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">Apply</button>
                <a class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium" href="<?= htmlspecialchars($apiBase) ?>?route=attendance/export&amp;month=<?= htmlspecialchars($month) ?>">Export Monthly CSV</a>
            </form>
        </section>

        <section class="grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="font-semibold text-lg mb-4">Employee Management</h2>
                <form id="employeeForm" class="grid grid-cols-2 gap-3">
                    <input required name="employee_code" class="border border-slate-300 rounded-lg p-2" placeholder="Employee Code">
                    <input required name="full_name" class="border border-slate-300 rounded-lg p-2" placeholder="Full Name">
                    <input required type="email" name="email" class="border border-slate-300 rounded-lg p-2" placeholder="Email Address">
                    <input required name="department" class="border border-slate-300 rounded-lg p-2" placeholder="Department">
                    <input required type="date" name="joining_date" class="border border-slate-300 rounded-lg p-2 col-span-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg py-2 col-span-2 font-medium">Save Employee</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="font-semibold text-lg mb-1">Attendance Management</h2>
                <p class="text-xs text-amber-600 mb-4">Policy: 1 hour permission is allowed once per month per employee (no loss of pay).</p>
                <form id="attendanceForm" class="grid grid-cols-2 gap-3">
                    <select name="employee_id" required class="border border-slate-300 rounded-lg p-2 col-span-2">
                        <option value="">Select Employee</option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?= (int) $employee['id'] ?>"><?= htmlspecialchars($employee['employee_code'] . ' - ' . $employee['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="date" required name="attendance_date" class="border border-slate-300 rounded-lg p-2">
                    <select name="status" required class="border border-slate-300 rounded-lg p-2">
                        <option value="present">Present</option>
                        <option value="half_day">Half Day</option>
                        <option value="leave">Leave</option>
                        <option value="permission_1h">1 Hour Permission</option>
                    </select>
                    <textarea name="remarks" placeholder="Remarks" class="border border-slate-300 rounded-lg p-2 col-span-2"></textarea>
                    <button class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg py-2 col-span-2 font-medium">Save Attendance</button>
                </form>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 overflow-x-auto">
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
                <?php if ($summary === []): ?>
                    <tr>
                        <td colspan="8" class="p-4 text-center text-slate-500">No attendance entries for this month yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($summary as $row): ?>
                        <tr class="border-b hover:bg-slate-50">
                            <td class="p-2"><?= htmlspecialchars((string) $row['employee_code']) ?></td>
                            <td class="p-2"><?= htmlspecialchars((string) $row['full_name']) ?></td>
                            <td class="p-2"><?= htmlspecialchars((string) $row['department']) ?></td>
                            <td class="p-2"><?= (int) $row['present_days'] ?></td>
                            <td class="p-2"><?= (int) $row['half_days'] ?></td>
                            <td class="p-2"><?= (int) $row['leave_days'] ?></td>
                            <td class="p-2"><?= (int) $row['one_hour_permissions'] ?></td>
                            <td class="p-2 font-semibold"><?= number_format((float) ($row['attendance_percentage'] ?? 0), 2) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

<script>
const API_BASE = <?= json_encode($apiBase, JSON_UNESCAPED_SLASHES) ?>;

function notify(message) {
    window.alert(message);
}

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
            : { error: `Unexpected response from server (HTTP ${response.status}).` };

        notify(result.message || result.error || 'Action complete');
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
