<?php

namespace App\Controllers;

use App\Core\DB;
use App\Core\Security;
use App\Core\View;
use App\Services\DashboardService;
use App\Services\ListQuery;
use RuntimeException;

final class AdminController extends BaseController
{
    public function index(): void
    {
        Security::requireRole('admin');
        View::render('admin/index', [
            'title' => 'Administrator overview',
            'stats' => DashboardService::admin(),
        ]);
    }

    public function accounts(): void
    {
        Security::requireRole('admin');
        $sorts = [
            'newest' => 'u.id DESC',
            'name' => 'u.name ASC, u.id ASC',
            'email' => 'u.email ASC, u.id ASC',
            'oldest' => 'u.id ASC',
        ];
        $filters = ListQuery::request($sorts);
        $role = (string) ($_GET['role'] ?? 'all');
        $status = (string) ($_GET['status'] ?? 'all');
        $where = 'FROM users u WHERE 1 = 1';
        $params = [];

        if ($filters['q'] !== '') {
            $where .= ' AND (u.name LIKE ? OR u.email LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if (in_array($role, ['admin', 'teacher', 'student'], true)) {
            $where .= ' AND u.role = ?';
            $params[] = $role;
        } else {
            $role = 'all';
        }
        if (in_array($status, ['active', 'inactive'], true)) {
            $where .= ' AND u.is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = 'all';
        }

        View::render('admin/accounts', [
            'title' => 'Manage accounts',
            'list' => ListQuery::fetch($where, $params, 'u.id, u.name, u.email, u.role, u.is_active', $sorts[$filters['sort']], $filters),
            'filters' => $filters,
            'roleFilter' => $role,
            'statusFilter' => $status,
            'edit' => isset($_GET['edit']) && is_string($_GET['edit']) && ctype_digit($_GET['edit'])
                ? DB::one('SELECT id, name, email, role, is_active FROM users WHERE id = ?', [(int) $_GET['edit']])
                : null,
        ]);
    }

    public function courses(): void
    {
        Security::requireRole('admin');
        $sorts = [
            'newest' => 'c.id DESC',
            'code' => 'c.code ASC, c.id ASC',
            'title' => 'c.title ASC, c.id ASC',
            'oldest' => 'c.id ASC',
        ];
        $filters = ListQuery::request($sorts);
        $status = (string) ($_GET['status'] ?? 'all');
        $where = 'FROM courses c WHERE 1 = 1';
        $params = [];

        if ($filters['q'] !== '') {
            $where .= ' AND (c.code LIKE ? OR c.title LIKE ?)';
            $params = ['%' . $filters['q'] . '%', '%' . $filters['q'] . '%'];
        }
        if (in_array($status, ['active', 'inactive'], true)) {
            $where .= ' AND c.is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = 'all';
        }

        View::render('admin/courses', [
            'title' => 'Manage courses',
            'edit' => isset($_GET['edit']) && is_string($_GET['edit']) && ctype_digit($_GET['edit'])
                ? DB::one('SELECT id, code, title, description, is_active FROM courses WHERE id = ?', [(int) $_GET['edit']])
                : null,
            'list' => ListQuery::fetch($where, $params, 'c.id, c.code, c.title, c.description, c.is_active', $sorts[$filters['sort']], $filters),
            'filters' => $filters,
            'statusFilter' => $status,
        ]);
    }

    public function sections(): void
    {
        Security::requireRole('admin');
        $sorts = [
            'newest' => 's.id DESC',
            'name' => 's.name ASC, s.id ASC',
            'course' => 'c.code ASC, s.name ASC',
            'oldest' => 's.id ASC',
        ];
        $filters = ListQuery::request($sorts);
        $status = (string) ($_GET['status'] ?? 'all');
        $where = 'FROM sections s JOIN courses c ON c.id = s.course_id JOIN users u ON u.id = s.teacher_id WHERE 1 = 1';
        $params = [];

        if ($filters['q'] !== '') {
            $where .= ' AND (s.name LIKE ? OR c.code LIKE ? OR u.name LIKE ? OR s.enrollment_code LIKE ?)';
            $params = array_fill(0, 4, '%' . $filters['q'] . '%');
        }
        if (in_array($status, ['active', 'inactive'], true)) {
            $where .= ' AND s.is_active = ?';
            $params[] = $status === 'active' ? 1 : 0;
        } else {
            $status = 'all';
        }

        View::render('admin/sections', [
            'title' => 'Manage sections',
            'edit' => isset($_GET['edit']) && is_string($_GET['edit']) && ctype_digit($_GET['edit'])
                ? DB::one('SELECT id, course_id, teacher_id, name, enrollment_code, is_active FROM sections WHERE id = ?', [(int) $_GET['edit']])
                : null,
            'list' => ListQuery::fetch($where, $params, 's.id, s.name, s.course_id, s.teacher_id, s.enrollment_code, s.is_active, c.code AS course_code, c.title AS course_title, u.name AS teacher_name', $sorts[$filters['sort']], $filters),
            'filters' => $filters,
            'statusFilter' => $status,
            'courses' => DB::all('SELECT id, code, title FROM courses ORDER BY code'),
            'teachers' => DB::all("SELECT id, name FROM users WHERE role = 'teacher' AND is_active = 1 ORDER BY name"),
        ]);
    }

    public function user(): void
    {
        $this->requirePost();
        Security::requireRole('admin');

        $this->execute(function () {
            $id = (int) ($_POST['id'] ?? 0);

            $name = $this->required('name', 150);
            $email = strtolower($this->required('email'));
            $role = $this->required('role', 20);
            $active = isset($_POST['is_active']) ? 1 : 0;

            if (
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                !in_array($role, ['admin', 'teacher', 'student'], true)
            ) {
                throw new RuntimeException('Invalid email or role.');
            }

            if ($id) {
                $existingUser = DB::one(
                    'SELECT * FROM users WHERE id = ?',
                    [$id]
                );

                if (!$existingUser) {
                    throw new RuntimeException('User not found.');
                }

                $currentUser = Security::user();

                if (
                    $id === (int) $currentUser['id'] &&
                    (!$active || $role !== 'admin')
                ) {
                    throw new RuntimeException(
                        'Cannot remove your own admin access.'
                    );
                }

                $password = (string) ($_POST['password'] ?? '');

                if ($password !== '' && strlen($password) < 8) {
                    throw new RuntimeException(
                        'Password must contain at least 8 characters.'
                    );
                }

                if ($password !== '') {
                    DB::exec(
                        'UPDATE users
                         SET name = ?,
                             email = ?,
                             role = ?,
                             is_active = ?,
                             password = ?
                         WHERE id = ?',
                        [
                            $name,
                            $email,
                            $role,
                            $active,
                            password_hash($password, PASSWORD_DEFAULT),
                            $id,
                        ]
                    );
                } else {
                    DB::exec(
                        'UPDATE users
                         SET name = ?,
                             email = ?,
                             role = ?,
                             is_active = ?
                         WHERE id = ?',
                        [
                            $name,
                            $email,
                            $role,
                            $active,
                            $id,
                        ]
                    );
                }
            } else {
                $password = (string) ($_POST['password'] ?? '');

                if (strlen($password) < 8) {
                    throw new RuntimeException(
                        'Password must contain at least 8 characters.'
                    );
                }

                DB::exec(
                    'INSERT INTO users (
                        name,
                        email,
                        password,
                        role,
                        is_active
                     ) VALUES (?, ?, ?, ?, ?)',
                    [
                        $name,
                        $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $role,
                        $active,
                    ]
                );
            }

            Security::redirect('/admin/accounts', 'Account saved.');
        }, '/admin/accounts');
    }

    public function course(): void
    {
        $this->requirePost();
        Security::requireRole('admin');

        $this->execute(function () {
            $id = (int) ($_POST['id'] ?? 0);

            $code = strtoupper($this->required('code', 40));
            $title = $this->required('title');
            $description = \input('description');
            $active = isset($_POST['is_active']) ? 1 : 0;

            if ($id) {
                DB::exec(
                    'UPDATE courses
                     SET code = ?,
                         title = ?,
                         description = ?,
                         is_active = ?
                     WHERE id = ?',
                    [
                        $code,
                        $title,
                        $description,
                        $active,
                        $id,
                    ]
                );
            } else {
                DB::exec(
                    'INSERT INTO courses (
                        code,
                        title,
                        description,
                        is_active
                     ) VALUES (?, ?, ?, ?)',
                    [
                        $code,
                        $title,
                        $description,
                        $active,
                    ]
                );
            }

            Security::redirect('/admin/courses', 'Course saved.');
        }, '/admin/courses');
    }

    public function section(): void
    {
        $this->requirePost();
        Security::requireRole('admin');

        $this->execute(function () {
            $id = (int) ($_POST['id'] ?? 0);

            $courseId = $this->id('course_id');
            $teacherId = $this->id('teacher_id');
            $name = $this->required('name', 120);
            $active = isset($_POST['is_active']) ? 1 : 0;

            $course = DB::one(
                'SELECT id
                 FROM courses
                 WHERE id = ?',
                [$courseId]
            );

            $teacher = DB::one(
                "SELECT id
                 FROM users
                 WHERE id = ?
                   AND role = 'teacher'
                   AND is_active = 1",
                [$teacherId]
            );

            if (!$course || !$teacher) {
                throw new RuntimeException(
                    'Select a valid course and active teacher.'
                );
            }

            $enrollmentCode = strtoupper(
                \input('enrollment_code', 32)
            );

            if ($id) {
                if ($enrollmentCode === '') {
                    throw new RuntimeException(
                        'Enrollment code is required when editing.'
                    );
                }

                DB::exec(
                    'UPDATE sections
                     SET course_id = ?,
                         teacher_id = ?,
                         name = ?,
                         enrollment_code = ?,
                         is_active = ?
                     WHERE id = ?',
                    [
                        $courseId,
                        $teacherId,
                        $name,
                        $enrollmentCode,
                        $active,
                        $id,
                    ]
                );
            } else {
                if ($enrollmentCode === '') {
                    $enrollmentCode = strtoupper(
                        bin2hex(random_bytes(5))
                    );
                }

                DB::exec(
                    'INSERT INTO sections (
                        course_id,
                        teacher_id,
                        name,
                        enrollment_code,
                        is_active
                     ) VALUES (?, ?, ?, ?, ?)',
                    [
                        $courseId,
                        $teacherId,
                        $name,
                        $enrollmentCode,
                        $active,
                    ]
                );
            }

            Security::redirect('/admin/sections', 'Section saved.');
        }, '/admin/sections');
    }
}