# ClassFlow LMS — Phase 1.2 UI Refresh

## Reference and design approach

Visual direction: https://studyq-tailwind.vercel.app/ (dashboard/navigation and sign-in layout inspiration). This is an independent implementation, not a copy of the StudyQ template or its assets. ClassFlow retains Bootstrap 5 and its native PHP custom MVC architecture to minimize regression risk; the purple/indigo theme is implemented in `public/assets/app.css` with a few progressive-enhancement behaviors in `public/assets/app.js`.

## Changes

- Consistent responsive application shell, vertical sidebar, topbar and user profile in Admin, Teacher, Student pages.
- Section-aware sidebar links; no new backend endpoints.
- Refreshed login with purple split-screen brand panel; mobile-first single-column fallback, accessible password visibility toggle.
- Reusable redesigned dashboard statistic cards with role-specific labels/icons and values from existing `DashboardService` queries.
- Consistent light tables, cards, badges, upload forms, course lists, action buttons and validation alerts across all pages.
- Layout supports reduced motion, keyboard focus visibility and mobile menu overlay with Escape key dismissal.

## Files changed

- `app/Views/partials/header.php`
- `app/Views/partials/footer.php`
- `app/Views/partials/dashboard_stats.php`
- `app/Views/auth/login.php`
- `app/Views/admin/index.php` (anchor IDs only)
- `app/Views/teacher/index.php` (anchor ID only)
- `app/Views/student/index.php` (anchor IDs only)
- `public/assets/app.css`
- `public/assets/app.js` (new)

## Installation

1. Back up your working ClassFlow project and database.
2. Unzip into a staging directory and review or merge into the **inner** `classflow/` PHP application folder (contains `composer.json`).
3. Keep existing `.env`, `vendor/`, `storage/uploads/`, and `.git` untouched.
4. No database migrations or npm build are required. The stylesheet uses Bootstrap 5 CDN and optional Google Fonts (may require network access).
5. Run `composer install` if dependencies are missing.
6. Run the tests and check every page on desktop and mobile before committing.

## Manual acceptance checklist

- [ ] Login renders desktop split layout and mobile single layout, including invalid-credentials alert.
- [ ] Show/Hide password works, submitting still authenticates each of three roles.
- [ ] Sidebar links and mobile menu work on Admin, Teacher, and Student pages.
- [ ] Admin: accounts, courses, section creation and tables remain functional.
- [ ] Teacher: list of assigned sections, upload materials, edit assignments with deadlines, submission list and grading still work.
- [ ] Student: enrollment, section materials, deadline badges, final submission, and released feedback still work.
- [ ] Test keyboard tab order/visible focus, 375px/768px/1280px widths, and file upload controls.
- [ ] Commit screenshots after running local MySQL-backed tests.

## Validation performed in build environment

- PHP syntax checks across project: passed.
- `php tests/smoke.php`: passed (bootstrap/CSRF/password utilities); MySQL unavailable.
- `php tests/dashboard_deadline.php`: passed (status query definitions); live DB not connected.
- Browser visual QA and end-to-end functional tests with MySQL: **not yet run**.

## Progress report note

This UI refresh was prepared after the professor's reporting period ending November 6, 2026. Date it to the actual implementation/verification/commit dates in the subsequent development addendum; do not backdate it.
