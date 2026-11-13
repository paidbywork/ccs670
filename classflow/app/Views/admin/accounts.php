<?php
$sortOptions = ['newest' => 'Newest', 'oldest' => 'Oldest', 'name' => 'Name A–Z', 'email' => 'Email A–Z'];
$extraOptions = [
    'role' => ['label' => 'Role', 'selected' => $roleFilter, 'values' => ['all' => 'All roles', 'admin' => 'Admin', 'teacher' => 'Teacher', 'student' => 'Student']],
    'status' => ['label' => 'Status', 'selected' => $statusFilter, 'values' => ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive']],
];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<p class="text-end">
<a class="btn btn-primary" href="#editor">+ Add new</a>
</p>
<div class="card">
<div class="card-body">
    <h2 class="h5">Accounts <span class="text-muted small">(<?= $list['total'] ?>)</span>
</h2>
    <div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>
    
<?php foreach ($list['items'] as $user): ?>

<tr>
<td><?= (int) $user['id'] ?>
</td>
<td><?= e($user['name']) ?>
</td>
<td><?= e($user['email']) ?>
</td>
<td><?= e(ucfirst($user['role'])) ?>
</td>
<td>
<span class="badge text-bg-<?= $user['is_active'] ? 'success' : 'secondary' ?>"><?= $user['is_active'] ? 'Active' : 'Inactive' ?>
</span>
</td>
<td>
<a class="btn btn-sm btn-outline-primary" href="/admin/accounts?edit=<?= (int) $user['id'] ?>#editor">Edit</a>
</td>
</tr>
<?php endforeach; ?>

    
<?php if (!$list['items']): ?>

<tr>
<td colspan="6" class="text-center text-muted py-4">No matching accounts.</td>
</tr>
<?php endif; ?>

    </tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>

<div class="card mb-4" id="editor">
<div class="card-body">
    <h2 class="h5"><?= $edit ? 'Edit account #' . (int) $edit['id'] : 'Create an account' ?>
</h2>
    <form method="post" action="/admin/users" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div class="col-md-2">
<label class="form-label" for="account-id">Edit ID</label>
<input id="account-id" name="id" type="number" min="1" class="form-control" value="<?= (int) ($edit['id'] ?? 0) ?: '' ?>" placeholder="New">
</div>
        <div class="col-md-4">
<label class="form-label" for="account-name">Full name</label>
<input id="account-name" name="name" maxlength="150" required class="form-control" value="<?= e($edit['name'] ?? '') ?>">
</div>
        <div class="col-md-6">
<label class="form-label" for="account-email">Email</label>
<input id="account-email" name="email" type="email" required class="form-control" value="<?= e($edit['email'] ?? '') ?>">
</div>
        <div class="col-md-3">
<label class="form-label" for="account-role">Role</label>
<select id="account-role" name="role" class="form-select">
<?php foreach (['teacher' => 'Teacher', 'student' => 'Student', 'admin' => 'Administrator'] as $key => $label): ?>

<option value="<?= e($key) ?>" <?= ($edit['role'] ?? 'teacher') === $key ? 'selected' : '' ?>><?= e($label) ?>
</option>
<?php endforeach; ?>

</select>
</div>
        <div class="col-md-5">
<label class="form-label" for="account-password">Password</label>
<input id="account-password" name="password" type="password" minlength="8" class="form-control" placeholder="Required for new; optional for edit">
</div>
        <div class="col-md-4 d-flex align-items-end">
<label class="form-check mb-2">
<input class="form-check-input" type="checkbox" name="is_active" <?= ($edit['is_active'] ?? 1) ? 'checked' : '' ?>> Active</label>
</div>
        <div class="col-12">
<button class="btn btn-primary">Save account</button>
<span class="small text-muted ms-2">Use an existing ID to update an account.</span>
</div>
    </form>
</div>
</div>
