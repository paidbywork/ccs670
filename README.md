# ClassFlow LMS

## Project description
ClassFlow is a custom MVC web application built with native, object-oriented PHP and MySQL. The MVP supports administrator-created accounts, teacher-owned courses, code-based student enrollment, materials, assignment submissions, grading, and feedback release.

## Objectives
- Demonstrate encapsulation, abstraction, inheritance where useful, and polymorphism in a PHP OOP application.
- Implement an end-to-end assignment submission → grading → feedback release workflow.
- Enforce authorization, referential integrity, unique enrollment, and unique final submission.

## Actors
- Admin: Manages user accounts, courses, sections, and teacher assignments.
- Teacher: Manages learning materials, assignments, submissions, grading, and feedback within assigned sections.
- Student: Enrolls in sections using enrollment codes, accesses learning materials, submits assignments, and views released grades and feedback.

## Key Features
ClassFlow LMS provides the following core features:

1. **User Authentication and Role-Based Access Control**
   - Secure login and logout for administrators, teachers, and students.
   - Role-specific dashboards and access permissions.

2. **User Account Management**
   - Administrators can create and manage teacher and student accounts.
   - Public user registration is disabled.

3. **Course Management**
   - Administrators can create, update, activate, and deactivate courses.
   - Each course can contain multiple class sections.
   - Administrators manage course information and organization.

4. **Section Management**
   - Administrators can create and manage sections under existing courses.
   - Each section is assigned to one teacher.
   - Teachers can manage learning activities within their assigned sections.
   - Each section has a unique enrollment code.

5. **Section Enrollment**
   - Students can enroll in sections using valid enrollment codes.
   - Students can enroll in multiple sections across different courses.
   - The system prevents duplicate enrollment in the same section.
   - Enrolled students can access learning materials and assignments within their respective sections.

6. **Learning Materials Management**
   - Teachers can upload and manage course materials.
   - Enrolled students can access and download learning resources.

7. **Assignment Management**
   - Teachers can create assignments with instructions and deadlines.
   - Students can view assignments and submit their work.
   - Only one final submission is permitted per assignment.

8. **Grading and Feedback**
   - Teachers can evaluate submissions and provide grades and feedback.
   - Teachers can save draft grades before releasing them.
   - Students can view their grades and feedback after release.

## UML artifacts
- [Use Case](uml/classflow-activity-diagram.png) (functional overview; formal UML actor-ellipse version may be drawn in diagrams.net)
- [Class Diagram](uml/classflow-class-diagram.png)
- [Sequence Diagram](uml/classflow-sequence-diagram.png)
- [Activity Diagram](uml/classflow-use-case-diagram.png)

## Technology stack
PHP 8.3+, custom MVC, PDO, MySQL 8, HTML/CSS, Bootstrap 5, Vanilla JavaScript, Composer PSR-4 autoloading, Git.

## Project Structure

The ClassFlow LMS follows a custom Model-View-Controller (MVC)
architecture using Native PHP and Object-Oriented Programming.

```text
classflow/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Services/
│   ├── Repositories/
│   ├── Interfaces/
│   ├── Middleware/
│   ├── Core/
│   └── Views/
├── bootstrap/
├── config/
├── database/
├── docs/
│   └── uml/
├── public/
│   ├── index.php
│   └── assets/
├── routes/
├── storage/
├── tests/
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

## Setup/execution (Phase 1 schema only)
1. Start MySQL 8.
2. Execute `database/schema.sql` using MySQL Workbench, phpMyAdmin, or `mysql -u root -p < database/schema.sql`.
3. Use `SHOW TABLES;` in the `classflow` database to confirm the seven tables.
4. Application routes, PHP models, seed accounts, and migrations will be implemented in Phase 2; this Phase 1 bundle is design/schema documentation, not a running app.
