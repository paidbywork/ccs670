<?php
namespace App\Controllers;
use App\Core\{DB,Security,View,Upload};
use App\Services\DashboardService;
use App\Services\ListQuery;
final class StudentController extends BaseController {
    /** Students can only access their own enrolled sections. */
    private function section(int $id): array
    {
        $student = Security::requireRole('student');
        $section = DB::one(
            'SELECT s.*, c.title AS course_title, c.code AS course_code
             FROM sections s
             JOIN courses c ON c.id = s.course_id
             JOIN enrollments e ON e.section_id = s.id
             WHERE s.id = ? AND e.student_id = ?',
            [$id, $student['id']]
        );

        if (!$section) {
            Security::abort(403);
        }

        return $section;
    }

 public function index(): void
 {
     $student = Security::requireRole('student');
     View::render('student/index', [
         'title' => 'Student overview',
         'stats' => DashboardService::student((int) $student['id']),
     ]);
 }

 public function sections(): void
 {
     $student = Security::requireRole('student');
     $sorts = ['newest' => 'e.id DESC', 'oldest' => 'e.id ASC', 'course' => 'c.code ASC, s.name ASC', 'name' => 's.name ASC, s.id ASC'];
     $filters = ListQuery::request($sorts);
     $where = 'FROM enrollments e JOIN sections s ON s.id = e.section_id JOIN courses c ON c.id = s.course_id WHERE e.student_id = ?';
     $params = [$student['id']];
     if ($filters['q'] !== '') {
         $where .= ' AND (s.name LIKE ? OR c.code LIKE ? OR c.title LIKE ?)';
         array_push($params, ...array_fill(0, 3, '%' . $filters['q'] . '%'));
     }
     View::render('student/sections', [
         'title' => 'My enrolled sections',
         'list' => ListQuery::fetch($where, $params, 's.id, s.name, s.is_active, c.code, c.title', $sorts[$filters['sort']], $filters),
         'filters' => $filters,
     ]);
 }

 public function join(): void
 {
     Security::requireRole('student');
     View::render('student/join', ['title' => 'Join a section']);
 }

 public function assignments(): void
 {
     $student = Security::requireRole('student');
     $sorts = [
         'newest' => 'a.id DESC',
         'oldest' => 'a.id ASC',
         'title' => 'a.title ASC, a.id ASC',
         'due' => 'a.due_at IS NULL ASC, a.due_at ASC, a.id DESC',
     ];
     $filters = ListQuery::request($sorts);
     $status = (string) ($_GET['status'] ?? 'all');
     $where = 'FROM assignments a JOIN enrollments e ON e.section_id = a.section_id '
         . 'JOIN sections s ON s.id = a.section_id JOIN courses c ON c.id = s.course_id '
         . 'LEFT JOIN submissions sb ON sb.assignment_id = a.id AND sb.student_id = e.student_id '
         . 'WHERE e.student_id = ?';
     $params = [$student['id']];
     if ($filters['q'] !== '') {
         $where .= ' AND (a.title LIKE ? OR s.name LIKE ? OR c.code LIKE ?)';
         array_push($params, ...array_fill(0, 3, '%' . $filters['q'] . '%'));
     }
     $statusClause = [
         'submitted' => 'sb.id IS NOT NULL',
         'overdue' => 'sb.id IS NULL AND a.due_at < NOW()',
         'due_soon' => 'sb.id IS NULL AND a.due_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 48 HOUR)',
         'open' => 'sb.id IS NULL AND a.due_at > DATE_ADD(NOW(), INTERVAL 48 HOUR)',
         'no_deadline' => 'sb.id IS NULL AND a.due_at IS NULL',
     ];
     if (array_key_exists($status, $statusClause)) {
         $where .= ' AND (' . $statusClause[$status] . ')';
     } else {
         $status = 'all';
     }
     $select = 'a.id, a.title, a.due_at, a.section_id, s.name AS section_name, c.code AS course_code, '
         . DashboardService::studentAssignmentStatusSql() . ' AS deadline_status';
     View::render('student/assignments', [
         'title' => 'My assignments',
         'list' => ListQuery::fetch($where, $params, $select, $sorts[$filters['sort']], $filters),
         'filters' => $filters,
         'statusFilter' => $status,
     ]);
 }

    public function enroll(): void
    {
        $this->requirePost();
        $student = Security::requireRole('student');

        $this->execute(function () use ($student): void {
            $code = strtoupper($this->required('enrollment_code', 32));
            $section = DB::one(
                "SELECT s.id
                 FROM sections s
                 JOIN courses c ON c.id = s.course_id
                 JOIN users t ON t.id = s.teacher_id
                 WHERE s.enrollment_code = ?
                   AND s.is_active = 1
                   AND c.is_active = 1
                   AND t.is_active = 1",
                [$code]
            );

            if (!$section) {
                throw new \RuntimeException('Enrollment code not found or section inactive.');
            }

            DB::exec(
                'INSERT IGNORE INTO enrollments (section_id, student_id) VALUES (?, ?)',
                [$section['id'], $student['id']]
            );

            Security::redirect('/student/sections/' . $section['id'], 'Section enrolled.');
        }, '/student/join');
    }

    public function detail(int $id): void
    {
        $section = $this->section($id);
        $student = Security::user();
        $materials = DB::all(
            'SELECT * FROM materials WHERE section_id = ? ORDER BY id DESC',
            [$id]
        );
        $assignments = DB::all(
            'SELECT a.*, sb.id AS submission_id, sb.submitted_at,
                    g.score, g.feedback, g.status, '
                . DashboardService::studentAssignmentStatusSql() . ' AS deadline_status
             FROM assignments a
             LEFT JOIN submissions sb ON sb.assignment_id = a.id AND sb.student_id = ?
             LEFT JOIN grades g ON g.submission_id = sb.id AND g.status = \'released\'
             WHERE a.section_id = ? ORDER BY a.id DESC',
            [$student['id'], $id]
        );

        View::render('student/section', [
            'title' => 'Section: ' . $section['name'],
            's' => $section,
            'materials' => $materials,
            'assignments' => $assignments,
        ]);
    }

    public function submit(): void
    {
        $this->requirePost();
        $student = Security::requireRole('student');
        $assignment = DB::one(
            'SELECT * FROM assignments WHERE id = ?',
            [$this->id('assignment_id')]
        );

        if (!$assignment) {
            Security::abort();
        }

        $section = $this->section((int) $assignment['section_id']);
        $fallback = '/student/sections/' . $section['id'];

        $this->execute(function () use ($student, $assignment, $fallback): void {
            $checkDeadline = function () use ($assignment): void {
                $expired = DB::one(
                    'SELECT id FROM assignments WHERE id = ? AND due_at IS NOT NULL AND due_at < NOW()',
                    [$assignment['id']]
                );

                if ($expired) {
                    throw new \RuntimeException('This assignment is past its deadline.');
                }
            };

            $checkDeadline();
            $existing = DB::one(
                'SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?',
                [$assignment['id'], $student['id']]
            );

            if ($existing) {
                throw new \RuntimeException('You have already made your final submission.');
            }

            $upload = Upload::save('file');

            try {
                $checkDeadline();
                DB::exec(
                    'INSERT INTO submissions (assignment_id, student_id, file_path, original_name)
                     VALUES (?, ?, ?, ?)',
                    [$assignment['id'], $student['id'], $upload['path'], $upload['name']]
                );
            } catch (\Throwable $exception) {
                @unlink(dirname(__DIR__, 2) . '/storage/uploads/' . $upload['path']);
                throw $exception;
            }

            Security::redirect($fallback, 'Final submission received.');
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
        $student = Security::requireRole('student');
        $submission = DB::one(
            'SELECT sb.*, a.section_id
             FROM submissions sb
             JOIN assignments a ON a.id = sb.assignment_id
             WHERE sb.id = ? AND sb.student_id = ?',
            [$id, $student['id']]
        );

        if (!$submission) {
            Security::abort(403);
        }

        $this->section((int) $submission['section_id']);
        Upload::send($submission['file_path'], $submission['original_name']);
    }
}
