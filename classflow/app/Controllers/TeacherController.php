<?php
namespace App\Controllers;
use App\Core\{DB,Security,View,Upload};
use App\Services\DashboardService;
use App\Services\ListQuery;
final class TeacherController extends BaseController {
    /** Only the assigned teacher may access this section. */
    private function section(int $id): array
    {
        $teacher = Security::requireRole('teacher');
        $section = DB::one(
            'SELECT s.*, c.title AS course_title, c.code AS course_code
             FROM sections s
             JOIN courses c ON c.id = s.course_id
             WHERE s.id = ? AND s.teacher_id = ?',
            [$id, $teacher['id']]
        );

        if (!$section) {
            Security::abort(403);
        }

        return $section;
    }

    private function assignment(int $id): array
    {
        $assignment = DB::one('SELECT * FROM assignments WHERE id = ?', [$id]);

        if (!$assignment) {
            Security::abort();
        }

        $this->section((int) $assignment['section_id']);
        return $assignment;
    }

 public function index(): void
 {
     $teacher = Security::requireRole('teacher');
     View::render('teacher/index', [
         'title' => 'Teacher overview',
         'stats' => DashboardService::teacher((int) $teacher['id']),
     ]);
 }

 public function sections(): void
 {
     $teacher = Security::requireRole('teacher');
     $sorts = ['newest' => 's.id DESC', 'oldest' => 's.id ASC', 'course' => 'c.code ASC, s.name ASC', 'name' => 's.name ASC, s.id ASC'];
     $filters = ListQuery::request($sorts);
     $where = 'FROM sections s JOIN courses c ON c.id = s.course_id WHERE s.teacher_id = ?';
     $params = [$teacher['id']];
     if ($filters['q'] !== '') {
         $where .= ' AND (s.name LIKE ? OR c.code LIKE ? OR c.title LIKE ?)';
         array_push($params, ...array_fill(0, 3, '%' . $filters['q'] . '%'));
     }
     $status = (string) ($_GET['status'] ?? 'all');
     if (in_array($status, ['active', 'inactive'], true)) {
         $where .= ' AND s.is_active = ?';
         $params[] = $status === 'active' ? 1 : 0;
     } else {
         $status = 'all';
     }
     View::render('teacher/sections', [
         'title' => 'My sections',
         'list' => ListQuery::fetch($where, $params, 's.id, s.name, s.is_active, c.code, c.title', $sorts[$filters['sort']], $filters),
         'filters' => $filters,
         'statusFilter' => $status,
     ]);
 }

 public function assignments(): void
 {
     $teacher = Security::requireRole('teacher');
     $sorts = [
         'newest' => 'a.id DESC',
         'oldest' => 'a.id ASC',
         'title' => 'a.title ASC, a.id ASC',
         'due' => 'a.due_at IS NULL ASC, a.due_at ASC, a.id DESC',
     ];
     $filters = ListQuery::request($sorts);
     $status = (string) ($_GET['status'] ?? 'all');
     $where = 'FROM assignments a JOIN sections s ON s.id = a.section_id JOIN courses c ON c.id = s.course_id WHERE s.teacher_id = ?';
     $params = [$teacher['id']];
     if ($filters['q'] !== '') {
         $where .= ' AND (a.title LIKE ? OR s.name LIKE ? OR c.code LIKE ?)';
         array_push($params, ...array_fill(0, 3, '%' . $filters['q'] . '%'));
     }
     $statusClause = [
         'past_due' => 'a.due_at < NOW()',
         'due_soon' => 'a.due_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)',
         'open' => 'a.due_at > DATE_ADD(NOW(), INTERVAL 48 HOUR)',
         'no_deadline' => 'a.due_at IS NULL',
     ];
     if (array_key_exists($status, $statusClause)) {
         $where .= ' AND ' . $statusClause[$status];
     } else {
         $status = 'all';
     }
     $select = 'a.id, a.title, a.due_at, a.section_id, s.name AS section_name, c.code AS course_code, '
         . DashboardService::teacherAssignmentStatusSql() . ' AS deadline_status';
     View::render('teacher/assignments', [
         'title' => 'Assignments',
         'list' => ListQuery::fetch($where, $params, $select, $sorts[$filters['sort']], $filters),
         'filters' => $filters,
         'statusFilter' => $status,
     ]);
 }

    public function detail(int $id): void
    {
        $section = $this->section($id);
        $materials = DB::all(
            'SELECT * FROM materials WHERE section_id = ? ORDER BY id DESC',
            [$id]
        );
        $assignments = DB::all(
            'SELECT a.*, ' . DashboardService::teacherAssignmentStatusSql()
                . ' AS deadline_status,
                   (SELECT COUNT(*) FROM submissions sb WHERE sb.assignment_id = a.id) AS submission_count
             FROM assignments a WHERE a.section_id = ? ORDER BY a.id DESC',
            [$id]
        );
        $roster = DB::all(
            'SELECT u.name, u.email, e.enrolled_at
             FROM enrollments e
             JOIN users u ON u.id = e.student_id
             WHERE e.section_id = ? ORDER BY u.name',
            [$id]
        );

        View::render('teacher/section', [
            'title' => 'Section: ' . $section['name'],
            's' => $section,
            'materials' => $materials,
            'assignments' => $assignments,
            'roster' => $roster,
        ]);
    }

    public function material(): void
    {
        $this->requirePost();
        $section = $this->section($this->id('section_id'));
        $fallback = '/teacher/sections/' . $section['id'];

        $this->execute(function () use ($section, $fallback): void {
            $title = $this->required('title');
            $description = \input('description');
            $upload = Upload::save('file');

            try {
                DB::exec(
                    'INSERT INTO materials (section_id, title, description, file_path, original_name)
                     VALUES (?, ?, ?, ?, ?)',
                    [$section['id'], $title, $description, $upload['path'], $upload['name']]
                );
            } catch (\Throwable $exception) {
                @unlink(dirname(__DIR__, 2) . '/storage/uploads/' . $upload['path']);
                throw $exception;
            }

            Security::redirect($fallback, 'Material uploaded.');
        }, $fallback);
    }

    public function assignmentSave(): void
    {
        $this->requirePost();
        $section = $this->section($this->id('section_id'));
        $fallback = '/teacher/sections/' . $section['id'];

        $this->execute(function () use ($section, $fallback): void {
            $id = (int) ($_POST['id'] ?? 0);
            $title = $this->required('title');
            $instructions = $this->required('instructions', 20000);
            $due = \input('due_at');

            if ($due !== '') {
                $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $due);

                if (!$parsed || $parsed->format('Y-m-d\TH:i') !== $due) {
                    throw new \RuntimeException('Invalid deadline.');
                }
            }

            $dueAt = $due === '' ? null : str_replace('T', ' ', $due) . ':00';

            if ($id > 0) {
                $assignment = $this->assignment($id);

                if ((int) $assignment['section_id'] !== (int) $section['id']) {
                    Security::abort(403);
                }

                DB::exec(
                    'UPDATE assignments SET title = ?, instructions = ?, due_at = ? WHERE id = ?',
                    [$title, $instructions, $dueAt, $id]
                );
            } else {
                DB::exec(
                    'INSERT INTO assignments (section_id, title, instructions, due_at) VALUES (?, ?, ?, ?)',
                    [$section['id'], $title, $instructions, $dueAt]
                );
            }

            Security::redirect($fallback, 'Assignment saved.');
        }, $fallback);
    }

    public function submissions(int $id): void
    {
        $assignment = $this->assignment($id);
        $items = DB::all(
            'SELECT sb.*, u.name AS student_name, g.id AS grade_id, g.score, g.feedback, g.status
             FROM submissions sb
             JOIN users u ON u.id = sb.student_id
             LEFT JOIN grades g ON g.submission_id = sb.id
             WHERE sb.assignment_id = ? ORDER BY sb.submitted_at DESC',
            [$id]
        );

        View::render('teacher/submissions', [
            'title' => 'Submissions',
            'a' => $assignment,
            'items' => $items,
        ]);
    }

    public function grade(): void
    {
        $this->requirePost();
        $teacher = Security::requireRole('teacher');
        $submissionId = $this->id('submission_id');
        $submission = DB::one(
            'SELECT sb.id, sb.assignment_id, a.section_id
             FROM submissions sb
             JOIN assignments a ON a.id = sb.assignment_id
             WHERE sb.id = ?',
            [$submissionId]
        );

        if (!$submission) {
            Security::abort();
        }

        $this->section((int) $submission['section_id']);
        $fallback = '/teacher/assignments/' . $submission['assignment_id'] . '/submissions';

        $this->execute(function () use ($teacher, $submissionId, $fallback): void {
            $score = \input('score');

            if (!is_numeric($score) || (float) $score < 0 || (float) $score > 100) {
                throw new \RuntimeException('Score must be between 0 and 100.');
            }

            $status = \input('status');

            if (!in_array($status, ['draft', 'released'], true)) {
                throw new \RuntimeException('Invalid grade status.');
            }

            $feedback = \input('feedback', 20000);
            DB::exec(
                "INSERT INTO grades (submission_id, teacher_id, score, feedback, status, released_at)
                 VALUES (?, ?, ?, ?, ?, IF(? = 'released', NOW(), NULL))
                 ON DUPLICATE KEY UPDATE
                    teacher_id = VALUES(teacher_id),
                    score = VALUES(score),
                    feedback = VALUES(feedback),
                    status = VALUES(status),
                    released_at = IF(VALUES(status) = 'released', COALESCE(released_at, NOW()), NULL),
                    graded_at = CURRENT_TIMESTAMP",
                [$submissionId, $teacher['id'], (float) $score, $feedback, $status, $status]
            );

            Security::redirect($fallback, 'Grade saved.');
        }, $fallback);
    }

    public function downloadMaterial(int $id): void
    {
        $material = DB::one('SELECT * FROM materials WHERE id = ?', [$id]);

        if (!$material) {
            Security::abort();
        }

        $this->section((int) $material['section_id']);
        Upload::send($material['file_path'], $material['original_name']);
    }

    public function downloadSubmission(int $id): void
    {
        $submission = DB::one(
            'SELECT sb.*, a.section_id
             FROM submissions sb JOIN assignments a ON a.id = sb.assignment_id WHERE sb.id = ?',
            [$id]
        );

        if (!$submission) {
            Security::abort();
        }

        $this->section((int) $submission['section_id']);
        Upload::send($submission['file_path'], $submission['original_name']);
    }
}
