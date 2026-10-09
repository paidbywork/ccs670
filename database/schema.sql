-- ClassFlow LMS | MVP Database Schema v1.2.1
-- Target: MySQL 8.0+ / InnoDB / utf8mb4
-- Compatibility: email VARCHAR(191) supports older 767-byte InnoDB index limits.
-- Section-specific learning materials and assignments.
-- Note: Apply to a new/empty database. This script does not migrate legacy tables.
-- CHECK constraints require MySQL 8.0.16+ (or a MariaDB version that supports CHECK).

CREATE DATABASE IF NOT EXISTS classflow_lms
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE classflow_lms;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_users_email UNIQUE (email)
) ENGINE=InnoDB;

CREATE TABLE courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_courses_code UNIQUE (code),
    CONSTRAINT fk_courses_created_by FOREIGN KEY (created_by)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE sections (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    section_code VARCHAR(50) NOT NULL,
    enrollment_code VARCHAR(40) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT uq_sections_course_code UNIQUE (course_id, section_code),
    CONSTRAINT uq_sections_enrollment_code UNIQUE (enrollment_code),
    CONSTRAINT fk_sections_course FOREIGN KEY (course_id)
        REFERENCES courses(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_sections_teacher FOREIGN KEY (teacher_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE enrollments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NOT NULL,
    enrolled_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_enrollments_student_section UNIQUE (student_id, section_id),
    CONSTRAINT fk_enrollments_student FOREIGN KEY (student_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_enrollments_section FOREIGN KEY (section_id)
        REFERENCES sections(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE materials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_materials_section FOREIGN KEY (section_id)
        REFERENCES sections(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE assignments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    section_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    instructions TEXT NOT NULL,
    max_score DECIMAL(8,2) NOT NULL,
    due_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_assignments_max_score CHECK (max_score > 0),
    CONSTRAINT fk_assignments_section FOREIGN KEY (section_id)
        REFERENCES sections(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    assignment_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    response_text TEXT NULL,
    file_path VARCHAR(500) NULL,
    submitted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_submissions_assignment_student UNIQUE (assignment_id, student_id),
    CONSTRAINT chk_submissions_content CHECK (
        (response_text IS NOT NULL AND CHAR_LENGTH(TRIM(response_text)) > 0)
        OR (file_path IS NOT NULL AND CHAR_LENGTH(TRIM(file_path)) > 0)
    ),
    CONSTRAINT fk_submissions_assignment FOREIGN KEY (assignment_id)
        REFERENCES assignments(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_submissions_student FOREIGN KEY (student_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE grades (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    submission_id BIGINT UNSIGNED NOT NULL,
    graded_by BIGINT UNSIGNED NOT NULL,
    score DECIMAL(8,2) NOT NULL,
    feedback TEXT NULL,
    status ENUM('draft', 'released') NOT NULL DEFAULT 'draft',
    graded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    released_at DATETIME NULL DEFAULT NULL,
    CONSTRAINT uq_grades_submission UNIQUE (submission_id),
    CONSTRAINT chk_grades_nonnegative CHECK (score >= 0),
    CONSTRAINT chk_grades_release_timestamp CHECK (
        (status = 'draft' AND released_at IS NULL)
        OR (status = 'released' AND released_at IS NOT NULL)
    ),
    CONSTRAINT fk_grades_submission FOREIGN KEY (submission_id)
        REFERENCES submissions(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_grades_teacher FOREIGN KEY (graded_by)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Critical application-layer business rules:
-- 1. Only admins can create user accounts, courses, sections, and teacher assignments.
-- 2. courses.created_by must reference an Admin; sections.teacher_id and
--    grades.graded_by must reference a Teacher; student_id must reference a Student.
-- 3. Validate course and section active status before allowing new enrollment.
-- 4. Only enrolled students may access materials or submit assignments in a section.
-- 5. Verify the student's section enrollment, assignment deadline, and section status
--    before inserting a final submission. No resubmission in the MVP.
-- 6. Only the section's assigned teacher may manage materials, assignments,
--    grades, and feedback, including release.
-- 7. Validate grades.score <= assignments.max_score in the grading service.
-- 8. Enforce status transitions and authorization transactionally as needed.
-- 9. Store uploaded files outside the public document root and authorize downloads.
-- 10. Preserve academic history when deactivating users/courses/sections.
