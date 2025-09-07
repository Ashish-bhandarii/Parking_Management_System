<div align="center">

# Parking Management System

Effortless reservations, real‑time slot availability, and a powerful admin dashboard.

![Project owner](./Images/Profile_v2.jpg)

[![Made with PHP](https://img.shields.io/badge/PHP-8.x-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Apache](https://img.shields.io/badge/Apache-HTTPD-D22128?logo=apache&logoColor=white)](https://httpd.apache.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](#license)

</div>

## Overview
A PHP/MySQL web app to manage parking areas, vehicle categories, pricing, and user reservations. Built for XAMPP on Windows, but deployable to any Apache + PHP host.

### Key Features
- User registration and login
- Search locations and view real‑time slot availability
- Create, edit, and pay for reservations
- Admin dashboard to manage users, messages, parking areas, fare rates, and vehicle categories
- Notifications and confirmations for users

## Demo Preview
Add screenshots or GIFs here.

```text
project/
├─ admin/                  # Admin dashboard pages
├─ user/                   # User pages and lightweight APIs
├─ includes/               # Shared includes (db, header, footer)
├─ css/                    # Stylesheets
├─ JS/                     # Client-side scripts
├─ Images/                 # Static images
├─ newdb_fixed.sql         # Database schema/seed
└─ index.php               # Entry point
```

## Tech Stack
- PHP 8.x (procedural)
- MySQL / MariaDB
- Apache (XAMPP)
- HTML, CSS, JavaScript

## Getting Started (XAMPP on Windows)
1. Clone or copy to `D:/xampp/htdocs/Parking_Management_System`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Create database and import schema:
   - Go to `http://localhost/phpmyadmin/`
   - Create a DB (e.g., `parking_db`)
   - Import `newdb_fixed.sql`
4. Configure connection in `includes/database.php` (host, user, password, database).
5. Run the app:
   - User: `http://localhost/Parking_Management_System/`
   - Admin: `http://localhost/Parking_Management_System/admin/`

## Configuration
`includes/database.php` holds DB credentials. You can also use environment variables via a `.env` file (keep it out of version control; see `.gitignore`).

## Security Checklist
- Validate and sanitize all inputs server‑side
- Use prepared statements for DB queries
- Restrict admin routes and protect sessions
- Hide detailed error messages in production

## Roadmap
- Payment gateway integration
- Slot analytics and reporting
- Role‑based access controls
- API hardening and rate limiting

## Contributing
Pull requests are welcome. For major changes, open an issue to discuss what you’d like to change.

1. Fork the repo
2. Create your feature branch (`git checkout -b feature/awesome`)
3. Commit your changes (`git commit -m "feat: add awesome"`)
4. Push to the branch (`git push origin feature/awesome`)
5. Open a Pull Request

## License
This project is licensed under the MIT License. See `LICENSE` (add one if needed).



