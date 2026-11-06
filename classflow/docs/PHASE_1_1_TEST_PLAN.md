# Phase 1.1 Local Verification Checklist

## Changes
- Admin: active teachers, active students, active courses, active sections, assignments, submissions.
- Teacher: assigned sections, assignments, submissions, ungraded submissions, past-due assignments.
- Student: enrolled sections, assigned work, submitted, pending, overdue, released grades.
- Section assignments: label Open, Due soon (within 48 hours), Past due / Overdue, No deadline; submitted student work takes priority.
- Teacher section page: submission counts for each assignment.
- Server rejects late submissions while preserving the one-final-submission constraint.

## Test with local MySQL and three accounts
1. Login as admin and compare dashboard counts to existing records.
2. Login as teacher with one assigned section; create assignments with deadlines: none, >48 hours, <48 hours, and already passed. Verify labels and counts.
3. Login as student enrolled in that section; verify Pending and Overdue metrics, plus each assignment's status.
4. Submit an Open or Due soon assignment; verify Submitted and total counters update, and resubmission is blocked.
5. Attempt late submission; verify form is hidden and POST is rejected server-side.
6. Teacher grades the submission in draft; confirm student released grade count does not change.
7. Teacher releases grade; confirm student's grade and feedback display, and released grade count increases.
8. Confirm teacher cannot see another teacher's sections and students cannot see other students' submissions.
9. Test a deadline exactly at the boundary; the server considers it expired only when due_at < NOW().
10. Confirm assignment editing still works; date/time entry uses the database server's local time zone.

## Commands
- php tests/smoke.php
- php tests/dashboard_deadline.php
- find app routes -name '*.php' -print0 | xargs -0 -n1 php -l

## Reporting evidence
Capture before/after screenshots for each role, deadline examples, test outcomes, and Git commit hashes/dates. Do not claim integration validation until the MySQL test is performed locally. Changes made after Nov 6 are outside a strictly Oct 24–Nov 6 report window unless the instructor authorizes inclusion.
