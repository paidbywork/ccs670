# ClassFlow Phase 1.1 merged application

This package uses the **inner `classflow/` PHP application** in your uploaded `classflow(1).zip` as its baseline, merged with Phase 1.1 updates.

## Added
- `app/Services/DashboardService.php`
- `app/Views/partials/dashboard_stats.php`
- `tests/dashboard_deadline.php`
- `docs/PHASE_1_1_TEST_PLAN.md`

## Updated
- `app/Controllers/AdminController.php` (existing formatted version preserved; dashboard statistics added)
- `app/Controllers/TeacherController.php` and `StudentController.php`
- Admin, teacher and student dashboard and section views

## Retained from local application
- Existing login, routes, DB schema, bootstrap, security, upload logic, and `composer.lock`
- Existing `composer.json` PHP requirement; no dependency update

## Deliberately omitted
- `.git` repository metadata, generated `vendor/` directory, and private content in `storage/uploads/`
- `.env` / local database credentials

## Install / verify
1. Back up your existing folder and database.
2. Merge these files into the **inner** app folder (the one containing `composer.json`).
3. Keep the local `.env` and stored uploads in their original locations.
4. From that application folder run `composer install`, then `php tests/smoke.php` and `php tests/dashboard_deadline.php`.
5. Complete the role-based and deadline scenarios in `docs/PHASE_1_1_TEST_PLAN.md`.

No SQL migration is required; the existing `assignments.due_at` column is reused. Deadline logic uses MySQL `NOW()`; ensure your PHP/MySQL and end-user local timezone expectations align. This merge has not been validated against your live MySQL database.
