<?php
namespace App\Services;

use App\Core\DB;

/** Dashboard queries are scoped to the authenticated actor's permitted records. */
final class DashboardService
{
    public static function admin(): array
    {
        $r = DB::one("SELECT
            (SELECT COUNT(*) FROM users WHERE role = 'teacher' AND is_active = 1) AS teachers,
            (SELECT COUNT(*) FROM users WHERE role = 'student' AND is_active = 1) AS students,
            (SELECT COUNT(*) FROM courses WHERE is_active = 1) AS courses,
            (SELECT COUNT(*) FROM sections WHERE is_active = 1) AS sections,
            (SELECT COUNT(*) FROM assignments) AS assignments,
            (SELECT COUNT(*) FROM submissions) AS submissions");
        return $r ?: [];
    }

    public static function teacher(int $teacherId): array
    {
        $r = DB::one("SELECT
            (SELECT COUNT(*) FROM sections WHERE teacher_id = ?) AS sections,
            (SELECT COUNT(*) FROM assignments a JOIN sections s ON s.id = a.section_id WHERE s.teacher_id = ?) AS assignments,
            (SELECT COUNT(*) FROM submissions sb JOIN assignments a ON a.id = sb.assignment_id JOIN sections s ON s.id = a.section_id WHERE s.teacher_id = ?) AS submissions,
            (SELECT COUNT(*) FROM submissions sb JOIN assignments a ON a.id = sb.assignment_id JOIN sections s ON s.id = a.section_id LEFT JOIN grades g ON g.submission_id = sb.id WHERE s.teacher_id = ? AND g.id IS NULL) AS ungraded,
            (SELECT COUNT(*) FROM assignments a JOIN sections s ON s.id = a.section_id WHERE s.teacher_id = ? AND a.due_at IS NOT NULL AND a.due_at < NOW()) AS past_due", array_fill(0, 5, $teacherId));
        return $r ?: [];
    }

    public static function student(int $studentId): array
    {
        $r = DB::one("SELECT
            (SELECT COUNT(*) FROM enrollments WHERE student_id = ?) AS sections,
            (SELECT COUNT(*) FROM assignments a JOIN enrollments e ON e.section_id = a.section_id WHERE e.student_id = ?) AS assignments,
            (SELECT COUNT(*) FROM submissions WHERE student_id = ?) AS submitted,
            (SELECT COUNT(*) FROM assignments a JOIN enrollments e ON e.section_id = a.section_id LEFT JOIN submissions sb ON sb.assignment_id = a.id AND sb.student_id = e.student_id WHERE e.student_id = ? AND sb.id IS NULL AND (a.due_at IS NULL OR a.due_at >= NOW())) AS pending,
            (SELECT COUNT(*) FROM assignments a JOIN enrollments e ON e.section_id = a.section_id LEFT JOIN submissions sb ON sb.assignment_id = a.id AND sb.student_id = e.student_id WHERE e.student_id = ? AND sb.id IS NULL AND a.due_at < NOW()) AS overdue,
            (SELECT COUNT(*) FROM grades g JOIN submissions sb ON sb.id = g.submission_id WHERE sb.student_id = ? AND g.status = 'released') AS graded", array_fill(0, 6, $studentId));
        return $r ?: [];
    }

    /** Avoid interpreting DB-local deadlines using the PHP server timezone. */
    public static function studentAssignmentStatusSql(): string
    {
        return "CASE WHEN sb.id IS NOT NULL THEN 'submitted' WHEN a.due_at IS NULL THEN 'no_deadline' WHEN a.due_at < NOW() THEN 'overdue' WHEN a.due_at <= DATE_ADD(NOW(), INTERVAL 48 HOUR) THEN 'due_soon' ELSE 'open' END";
    }

    public static function teacherAssignmentStatusSql(): string
    {
        return "CASE WHEN a.due_at IS NULL THEN 'no_deadline' WHEN a.due_at < NOW() THEN 'past_due' WHEN a.due_at <= DATE_ADD(NOW(), INTERVAL 48 HOUR) THEN 'due_soon' ELSE 'open' END";
    }
}
