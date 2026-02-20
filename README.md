# Office Attendance Pro (PHP + Tailwind + MySQL + JS)

A professional attendance management website for offices with 100+ employees.

## Features

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
  index.php              # UI dashboard
  api/index.php          # REST-like API router
src/
  Core/                  # Config, DB, Session, BaseModel, ApiResponse
  Models/                # Employee, Attendance models
  Services/              # Attendance business logic
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
   - UI: `http://localhost:8000`
   - APIs:
     - `GET /api/index.php?route=employees`
     - `POST /api/index.php?route=employees`
     - `PATCH /api/index.php?route=employees/{id}`
     - `POST /api/index.php?route=attendance`
     - `GET /api/index.php?route=attendance/summary&month=YYYY-MM`
     - `GET /api/index.php?route=attendance/export&month=YYYY-MM`

## Attendance Percentage Formula

For each employee in selected month:

- Present = 1 day credit
- Half day = 0.5 day credit
- 1-hour permission = 1 day credit (no loss of pay)
- Leave = 0 day credit

`attendance_percentage = (credited_days / total_marked_entries) * 100`

## Notes

- Business rule enforced: an employee can use `permission_1h` only once per month.
- Code is modular, ready for expansion (authentication, role management, pagination, audit logs).

## Apache / XAMPP Hosting Note

If you open the repository root in Apache and see a directory listing, use one of these approaches:

- Preferred: set your VirtualHost `DocumentRoot` to the `public/` folder.
- Included fallback: this project now includes root `index.php` + `.htaccess` that forwards traffic to `public/`.

So you can open either:
- `http://localhost/your-project/public/`
- or `http://localhost/your-project/` (auto-forwarded)

