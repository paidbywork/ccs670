<?php
$sortOptions = ['newest' => 'Newest', 'oldest' => 'Oldest', 'course' => 'Course code', 'name' => 'Section name'];
$extraOptions = ['status' => ['label' => 'Status', 'selected' => $statusFilter, 'values' => ['all' => 'All', 'active' => 'Active', 'inactive' => 'Inactive']]];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<div class="card">
<div class="card-body">
<h2 class="h5">Assigned sections (<?= $list['total'] ?>)</h2>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Course</th>
<th>Section</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php foreach ($list['items'] as $section): ?>

<tr>
<td><?= e($section['code'] . ' — ' . $section['title']) ?>
</td>
<td><?= e($section['name']) ?>
</td>
<td><?= $section['is_active'] ? 'Active' : 'Inactive' ?>
</td>
<td>
<a class="btn btn-sm btn-outline-primary" href="/teacher/sections/<?= (int) $section['id'] ?>">Open section</a>
</td>
</tr>
<?php endforeach; ?>


<?php if (!$list['items']): ?>

<tr>
<td colspan="4" class="text-center text-muted py-4">No matching sections.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>
