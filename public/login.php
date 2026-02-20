<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Models\User;
use App\Services\AuthService;

$authService = new AuthService(new User());
$authService->bootDefaultAdmin();

if ($authService->check()) {
    header('Location: index.php');
    exit;
}

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$basePath = $basePath === '' ? '' : $basePath;
$apiBase = $basePath . '/api/index.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Office Attendance Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center px-4">
<div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-7">
    <h1 class="text-2xl font-bold text-slate-900">Office Attendance Pro</h1>
    <p class="text-slate-500 text-sm mt-1">Sign in to manage attendance operations.</p>

    <form id="loginForm" class="mt-6 space-y-4">
        <div>
            <label class="text-sm font-medium text-slate-700">Username</label>
            <input name="username" required class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2" placeholder="admin">
        </div>
        <div>
            <label class="text-sm font-medium text-slate-700">Password</label>
            <input name="password" type="password" required class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2" placeholder="********">
        </div>
        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-medium">Sign In</button>
    </form>

    <div class="mt-5 text-xs text-slate-500 bg-slate-50 border rounded-lg p-3">
        Default credentials: <span class="font-semibold">admin / admin@123</span>
    </div>
</div>

<script>
const API_BASE = <?= json_encode($apiBase, JSON_UNESCAPED_SLASHES) ?>;

document.getElementById('loginForm').addEventListener('submit', async (event) => {
    event.preventDefault();
    const payload = Object.fromEntries(new FormData(event.target).entries());

    const response = await fetch(`${API_BASE}?route=auth/login`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    const contentType = response.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await response.json() : {error: 'Unexpected response'};

    if (!response.ok) {
        alert(data.error || 'Login failed');
        return;
    }

    window.location.href = 'index.php';
});
</script>
</body>
</html>
