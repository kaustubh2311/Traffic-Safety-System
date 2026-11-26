# Team 6 — Intelligent Traffic Violation Reporting and Road Safety Management System

A beginner-friendly PHP + MySQL/MariaDB academic prototype for local use with XAMPP.

## Included features

- Public homepage with project overview
- Citizen report submission with validation and optional evidence photo
- Private reference number for report tracking (reference + reporting email required)
- One-time initial administrator setup and password-hashed login
- Authority dashboard, report search/filter, review statuses, remarks and status history
- Basic analytics by violation category, current status and frequently entered location text
- CSRF tokens, prepared SQL statements, output escaping, role checks and restricted evidence storage
- Responsive styling without requiring Bootstrap or Composer to run

## Requirements

- Windows with XAMPP installed
- Apache and MySQL (MariaDB) from XAMPP
- PHP with PDO MySQL and Fileinfo enabled (both are usually enabled in current XAMPP builds)
- A modern browser

## Installation (Windows / XAMPP)

1. Extract the ZIP into `C:\xampp\htdocs\` so the final folder is:
   `C:\xampp\htdocs\traffic-safety-system\`
2. Open the XAMPP Control Panel. Start **Apache** and **MySQL**.
3. Open `http://localhost/phpmyadmin/` in your browser.
4. Select **Import**, choose `database/schema.sql` from this project folder, and click **Import** / **Go**. The script creates `traffic_safety_db` and the required tables.
5. Open `http://localhost/traffic-safety-system/setup_admin.php`.
6. Create the first administrator with your own name, email and a strong password (10+ characters). No default admin password is included.
7. **Delete `setup_admin.php` from the project folder once the account is created.** The page also disables itself once an authority account exists, but deleting it is recommended.
8. Open `http://localhost/traffic-safety-system/` to view the homepage.
9. Submit a test report at `http://localhost/traffic-safety-system/report.php`.
10. Sign in at `http://localhost/traffic-safety-system/admin/login.php` and open the dashboard to review it.

## Important configuration

- Default XAMPP database settings are in `config/config.php`: host `127.0.0.1`, database `traffic_safety_db`, user `root`, blank password. Change these values if your local database uses a password.
- `BASE_URL` is `/traffic-safety-system`. If you rename the project folder, update `BASE_URL` in `config/config.php`.
- Evidence files are stored under `storage/evidence/`, which has an `.htaccess` rule preventing direct access. Signed-in authority users can view evidence through `evidence.php`.
- If uploads fail, check the PHP upload limits in `C:\xampp\php\php.ini` (`upload_max_filesize` and `post_max_size`) and restart Apache after changing settings. The application accepts files up to 5 MB.

## Basic test checklist

1. Homepage opens at the localhost URL.
2. A report with valid data is saved and a reference number is displayed.
3. Tracking works when both the reference number and matching email are supplied.
4. Tracking with an incorrect reference/email pair does not reveal report details.
5. An administrator can sign in and see the submitted report.
6. Invalid status changes or invalid form data are rejected.
7. A submitted evidence image is only viewable after authority sign-in.
8. Analytics counts update after new reports arrive.

## Project limitations and responsible use

- This is an academic prototype. It is not connected to a police database, vehicle-registration system, payment gateway or government challan service.
- “Verified” is an internal demo workflow status and does not mean a legal offence has been established.
- `penalty_amount` is an optional illustrative estimate only. Do not use it to demand payment or issue an official fine.
- Analytics count reports and group the location text entered by reporters. They do not establish verified accident hotspots.
- Only submit evidence lawfully and when safe. Do not confront drivers or collect evidence while driving.
- Before real deployment, a qualified review is required for privacy, retention, access control, logging, hosting, backups, legal authority and abuse prevention.

## Beginner learning path

1. Start by reading `index.php` and `assets/css/style.css`.
2. Read `report.php` to understand the form HTML.
3. Read `submit_report.php` to see server-side validation, file handling and database inserts.
4. Read `config/database.php` to understand the PDO connection.
5. Read `admin/login.php`, then `admin/view_report.php` and `admin/update_report.php` to understand sessions and protected admin actions.

No Composer packages or Bootstrap CDN are required for this version; fewer moving parts make it easier to run on a new laptop. Bootstrap can be added later if your team wants to practise it.
