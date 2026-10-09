# ClassFlow LMS

## Project description
ClassFlow is a custom MVC web application built with native, object-oriented PHP and MySQL. The MVP supports administrator-created accounts, teacher-owned courses, code-based student enrollment, materials, assignment submissions, grading, and feedback release.

## Objectives
- Demonstrate encapsulation, abstraction, inheritance where useful, and polymorphism in a PHP OOP application.
- Implement an end-to-end assignment submission → grading → feedback release workflow.
- Enforce authorization, referential integrity, unique enrollment, and unique final submission.

## Actors
- Admin: account management. 
- Teacher: courses, materials, assignments, grades. 
- Student: enrollment, materials, submissions, released feedback.

## Key Features
ClassFlow LMS provides the following core features:

1. **User Authentication and Role-Based Access Control**
   - Secure login and logout for administrators, teachers, and students.
   - Role-specific dashboards and access permissions.

2. **User Account Management**
   - Administrators can create and manage teacher and student accounts.
   - Public user registration is disabled.

3. **Course Management**
   - Teachers can create and manage their assigned courses.
   - Each course has a unique enrollment code.

4. **Course Enrollment**
   - Students can join courses using a valid enrollment code.
   - The system prevents duplicate enrollments.

5. **Learning Materials Management**
   - Teachers can upload and manage course materials.
   - Enrolled students can access and download learning resources.

6. **Assignment Management**
   - Teachers can create assignments with instructions and deadlines.
   - Students can view assignments and submit their work.
   - Only one final submission is permitted per assignment.

7. **Grading and Feedback**
   - Teachers can evaluate submissions and provide grades and feedback.
   - Teachers can save draft grades before releasing them.
   - Students can view their grades and feedback after release.

## UML artifacts
- [ERD](uml/erd.mmd)
- [Use Case](uml/use-case.mmd) (functional overview; formal UML actor-ellipse version may be drawn in diagrams.net)
- [Class Diagram](uml/class-diagram.mmd)
- [Sequence Diagram](uml/sequence.mmd)
- [Activity Diagram](uml/activity.mmd)

## Database
See [`../database/schema.sql`](../database/schema.sql) for runnable MySQL 8 DDL. It implements seven entity tables, unique constraints, indexes, check constraints and foreign keys. Cross-table role, ownership, membership, deadline and score-limit rules are enforced in PHP services with transactions.

## Technology stack
PHP 8.3+, custom MVC, PDO, MySQL 8, HTML/CSS, Bootstrap 5, Vanilla JavaScript, Composer PSR-4 autoloading, Git.

## Proposed project structure
`app/{Controllers,Models,Services,Repositories,Interfaces,Middleware,Core,Views}`; 
`bootstrap`; 
`config`; 
`database`; 
`docs/uml`; 
`public`; 
`routes`; 
`storage/uploads`; 
`tests`.

## Setup/execution (Phase 1 schema only)
1. Start MySQL 8.
2. Execute `database/schema.sql` using MySQL Workbench, phpMyAdmin, or `mysql -u root -p < database/schema.sql`.
3. Use `SHOW TABLES;` in the `classflow` database to confirm the seven tables.
4. Application routes, PHP models, seed accounts, and migrations will be implemented in Phase 2; this Phase 1 bundle is design/schema documentation, not a running app.

## Security and implementation notes
- Document root must be `public/`; files in `storage/uploads/` are private.
- Hash passwords using PHP `password_hash()`, verify via `password_verify()`.
- Validate authentication, authorization, and CSRF tokens for state-changing operations.
- Validate file MIME/content, extensions, upload size; generate random storage names.
- Use PDO prepared statements. Escape dynamic HTML output.
- Use a consistent configured timezone and application-generated submission time.
- Insert final submissions transactionally; the unique `(assignment_id, student_id)` constraint handles concurrent requests.
- No direct destructive deletion of users, courses or academic records in MVP.

## Design notes
- The ER diagram is conceptual; detailed constraints are expressed in DDL (Data Definition Language).
- Fixed user roles are stored as an enum for MVP (no separate role or subtype table).
- Course `code` identifies a course; `enrollment_code` is the secret joining code.
- One submission can have zero or one grade; a released grade must have `released_at`.
- Check `grades.score <= assignments.max_score` at the service layer: it spans tables.
- File metadata includes private storage path, original filename, MIME, and size.
- MVP excludes quizzes, messaging, video, email notifications, attendance, grade aggregation, and repeat submissions.
