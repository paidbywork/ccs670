<?php
$sortOptions = ['newest' => 'Newest', 'oldest' => 'Oldest', 'code' => 'Code A–Z', 'title' => 'Title A–Z'];
$extraOptions = ['status' => ['label' => 'Status', 'selected' => $statusFilter, 'values' => ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive']]];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<p class="text-end">
<a class="btn btn-primary" href="#editor">+ Add new</a>
</p>
<div class="card">
<div class="card-body">
<h2 class="h5">Courses (<?= $list['total'] ?>)</h2>
    <div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th>
<th>Code</th>
<th>Course</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
    
<?php foreach ($list['items'] as $course): ?>

<tr>
<td><?= (int) $course['id'] ?>
</td>
<td><?= e($course['code']) ?>
</td>
<td><?= e($course['title']) ?>
</td>
<td><?= $course['is_active'] ? 'Active' : 'Inactive' ?>
</td>
<td>
<a class="btn btn-sm btn-outline-primary" href="/admin/courses?edit=<?= (int) $course['id'] ?>#editor">Edit</a>
</td>
</tr>
<?php endforeach; ?>

    
<?php if (!$list['items']): ?>

<tr>
<td colspan="5" class="text-center text-muted py-4">No matching courses.</td>
</tr>
<?php endif; ?>

    </tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>

<div class="card mb-4" id="editor">
<div class="card-body">
    <h2 class="h5"><?= $edit ? 'Edit course #' . (int) $edit['id'] : 'Create a course' ?>
</h2>
    <form method="post" action="/admin/courses" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div class="col-md-2">
<label class="form-label">Edit ID</label>
<input name="id" type="number" min="1" class="form-control" value="<?= (int) ($edit['id'] ?? 0) ?: '' ?>" placeholder="New">
</div>
        <div class="col-md-3">
<label class="form-label">Course code</label>
<input name="code" maxlength="40" required class="form-control" value="<?= e($edit['code'] ?? '') ?>">
</div>
        <div class="col-md-7">
<label class="form-label">Course title</label>
<input name="title" required class="form-control" value="<?= e($edit['title'] ?? '') ?>">
</div>
        <div class="col-md-9">
<label class="form-label">Description</label>
<textarea name="description" class="form-control" rows="2"><?= e($edit['description'] ?? '') ?>
</textarea>
</div>
        <div class="col-md-3 d-flex align-items-center">
<label class="form-check">
<input class="form-check-input" name="is_active" type="checkbox" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> Active</label>
</div>
        <div class="col-12">
<button class="btn btn-primary">Save course</button>
</div>
    </form>
</div>
</div>
