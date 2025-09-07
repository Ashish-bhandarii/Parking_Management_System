# Parking Reservation and Management System

A PHP/MySQL web app for managing parking areas, vehicle categories, user reservations, and admin operations. Built to run on XAMPP (Apache + PHP + MySQL).

## Features
- User registration/login
- Search locations and available slots
- Make, edit, and pay reservations
- Admin dashboard to manage users, messages, parking areas, rates, and categories

## Tech Stack
- PHP (procedural)
- MySQL
- XAMPP (Apache + MariaDB)
- HTML/CSS/JS

## Project Structure
- `includes/` shared includes like `database.php`, `header.php`, `footer.php`
- `user/` user-facing pages and APIs
- `admin/` admin dashboard pages
- `css/` stylesheets
- `JS/` client-side scripts
- `newdb_fixed.sql` database schema and seed data

## Local Setup (XAMPP on Windows)
1. Place this folder under `D:/xampp/htdocs/project` (already done if you see this).
2. Start Apache and MySQL in XAMPP Control Panel.
3. Create database and import schema:
   - Open phpMyAdmin: http://localhost/phpmyadmin/
   - Create a database (e.g., `project`)
   - Import `newdb_fixed.sql`
4. Configure DB connection:
   - Edit `includes/database.php` with your MySQL host, user, password, and database name.
5. Visit the app:
   - User: http://localhost/project/
   - Admin: http://localhost/project/admin/ (after logging in as an admin)

## Environment Configuration
`includes/database.php` contains the connection settings. If you prefer environment variables, create a `.env` (not committed) and load from there; ensure `.env` stays ignored by git.

## Development
- Static assets live under `css/` and `JS/`.
- Keep PHP logic minimal in views; reuse includes from `includes/`.

## Deployment
- Use an Apache/PHP host with MySQL.
- Update DB credentials for production.
- Secure admin area and validate user inputs.

## License
Add your preferred license (e.g., MIT) or remove this section.


