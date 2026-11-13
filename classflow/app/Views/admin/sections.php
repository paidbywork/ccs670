<?php
$sortOptions = ['newest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Section name', 'course' => 'Course code'];
$extraOptions = ['status' => ['label' => 'Status', 'selected' => $statusFilter, 'values' => ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive']]];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<p class="text-end">
<a class="btn btn-primary" href="#editor">+ Add new</a>
</p>
<div class="card">
<div class="card-body">
<h2 class="h5">Sections (<?= $list['total'] ?>)</h2>
    <div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th>
<th>Course</th>
<th>Section</th>
<th>Teacher</th>
<th>Enrollment code</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
    
<?php foreach ($list['items'] as $section): ?>

<tr>
<td><?= (int) $section['id'] ?>
</td>
<td><?= e($section['course_code']) ?>
</td>
<td><?= e($section['name']) ?>
</td>
<td><?= e($section['teacher_name']) ?>
</td>
<td>
<code><?= e($section['enrollment_code']) ?>
</code>
</td>
<td><?= $section['is_active'] ? 'Active' : 'Inactive' ?>
</td>
<td>
<a class="btn btn-sm btn-outline-primary" href="/admin/sections?edit=<?= (int) $section['id'] ?>#editor">Edit</a>
</td>
</tr>
<?php endforeach; ?>

    
<?php if (!$list['items']): ?>

<tr>
<td colspan="7" class="text-center text-muted py-4">No matching sections.</td>
</tr>
<?php endif; ?>

    </tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>

<div class="card mb-4" id="editor">
<div class="card-body">
    <h2 class="h5"><?= $edit ? 'Edit section #' . (int) $edit['id'] : 'Create a section' ?>
</h2>
    <form method="post" action="/admin/sections" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div class="col-md-2">
<label class="form-label">Edit ID</label>
<input type="number" min="1" name="id" class="form-control" value="<?= (int) ($edit['id'] ?? 0) ?: '' ?>" placeholder="New">
</div>
        <div class="col-md-4">
<label class="form-label">Course</label>
<select required class="form-select" name="course_id">
<option value="">Choose course</option>
<?php foreach ($courses as $course): ?>

<option value="<?= (int) $course['id'] ?>" <?= (int) ($edit['course_id'] ?? 0) === (int) $course['id'] ? 'selected' : '' ?>><?= e($course['code'] . ' — ' . $course['title']) ?>
</option>
<?php endforeach; ?>

</select>
</div>
        <div class="col-md-3">
<label class="form-label">Teacher</label>
<select required class="form-select" name="teacher_id">
<option value="">Choose teacher</option>
<?php foreach ($teachers as $teacher): ?>

<option value="<?= (int) $teacher['id'] ?>" <?= (int) ($edit['teacher_id'] ?? 0) === (int) $teacher['id'] ? 'selected' : '' ?>><?= e($teacher['name']) ?>
</option>
<?php endforeach; ?>

</select>
</div>
        <div class="col-md-3">
<label class="form-label">Section name</label>
<input required name="name" maxlength="120" class="form-control" value="<?= e($edit['name'] ?? '') ?>">
</div>
        <div class="col-md-4">
<label class="form-label">Enrollment code</label>
<input name="enrollment_code" maxlength="32" class="form-control" value="<?= e($edit['enrollment_code'] ?? '') ?>" placeholder="Auto-generated for new sections">
</div>
        <div class="col-md-4 d-flex align-items-center">
<label class="form-check">
<input class="form-check-input" type="checkbox" name="is_active" checked> Active</label>
</div>
        <div class="col-12">
<button class="btn btn-primary">Save section</button>
</div>
    </form>
</div>
</div>
