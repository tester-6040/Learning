# Office Attendance Pro (PHP + Tailwind + MySQL + JS)

A professional attendance management website for offices with 100+ employees.

## Features

- Secure login authorization (session based).
- Employee master management (create/update/list).
- Attendance marking with statuses:
  - Present
  - Half Day
  - Leave
  - **1 Hour Permission** (allowed **only once per month** per employee, no loss of pay)
- Monthly dashboard with:
  - Present/Half day/Leave/1-hour permission counts
  - Attendance percentage
- CSV export for monthly attendance reports.
- Reusable architecture:
  - `BaseModel` for common query methods (`select`, `selectOne`, `execute`, `insert`)
  - `SessionManager` helper
  - API response helper for standardized JSON output

## Tech Stack

- PHP 8+
- MySQL
- Tailwind CSS (CDN)
- Vanilla JavaScript (fetch API)

## Project Structure

```txt
public/
  index.php              # Protected dashboard UI
  login.php              # Authentication screen
  api/index.php          # API router
  .htaccess              # Apache entry rules for /public
src/
  Core/                  # Config, DB, Session, BaseModel, ApiResponse
  Models/                # User, Employee, Attendance models
  Services/              # Auth + Attendance business logic
  Controllers/           # API controllers
database/schema.sql      # DB schema
bootstrap.php            # PSR-4 style autoloading
```

## Setup

1. Create DB tables:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. Configure environment variables (optional, defaults shown):
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_NAME=attendance_db`
   - `DB_USER=root`
   - `DB_PASS=`
   - `DB_CHARSET=utf8mb4`

3. Run local server:
   ```bash
   php -S 0.0.0.0:8000 -t public
   ```

4. Open:
   - Login: `http://localhost:8000/login.php`
   - Dashboard: `http://localhost:8000/`

5. Default login:
   - Username: `admin`
   - Password: `admin@123`

## API Endpoints

- `POST /api/index.php?route=auth/login`
- `POST /api/index.php?route=auth/logout`
- `GET /api/index.php?route=auth/me`
- `GET /api/index.php?route=employees`
- `POST /api/index.php?route=employees`
- `PATCH /api/index.php?route=employees/{id}`
- `POST /api/index.php?route=attendance`
- `GET /api/index.php?route=attendance/summary&month=YYYY-MM`
- `GET /api/index.php?route=attendance/export&month=YYYY-MM`

> All employee/attendance endpoints require login session authorization.

## Attendance Percentage Formula

For each employee in selected month:

- Present = 1 day credit
- Half day = 0.5 day credit
- 1-hour permission = 1 day credit (no loss of pay)
- Leave = 0 day credit

`attendance_percentage = (credited_days / total_marked_entries) * 100`

## Apache / XAMPP Hosting Notes

If you see **Index of ...** or **Forbidden**:

- Do not open filesystem-style URLs like `http://localhost/C:/xampp/...`.
- Place project under Apache web root (for example `C:\xampp\htdocs\Learning`).
- Open via: `http://localhost/Learning/` or `http://localhost/Learning/public/`.
- Prefer VirtualHost `DocumentRoot` pointing to the project's `public/` directory.
- This project includes root `index.php`, root `.htaccess`, and `public/.htaccess` for compatibility.

## Notes

- Business rule enforced: an employee can use `permission_1h` only once per month.
- Code is modular and ready for expansion (roles, password reset, audit logs, reports).
