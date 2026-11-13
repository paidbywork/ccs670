<?php
$sortOptions = ['newest' => 'Newest', 'oldest' => 'Oldest', 'title' => 'Title A–Z', 'due' => 'Due date'];
$extraOptions = ['status' => ['label' => 'Status', 'selected' => $statusFilter, 'values' => ['all' => 'All', 'open' => 'Open', 'due_soon' => 'Due soon', 'overdue' => 'Overdue', 'submitted' => 'Submitted', 'no_deadline' => 'No deadline']]];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<div class="card">
<div class="card-body">
<h2 class="h5">My assignments (<?= $list['total'] ?>)</h2>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Title</th>
<th>Course</th>
<th>Section</th>
<th>Deadline</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody>

<?php foreach ($list['items'] as $assignment): ?>

<tr>
<td><?= e($assignment['title']) ?>
</td>
<td><?= e($assignment['course_code']) ?>
</td>
<td><?= e($assignment['section_name']) ?>
</td>
<td><?= e($assignment['due_at'] ?? 'No deadline') ?>
</td>
<td>
<span class="badge text-bg-<?= $assignment['deadline_status'] === 'submitted' ? 'success' : ($assignment['deadline_status'] === 'overdue' ? 'danger' : ($assignment['deadline_status'] === 'due_soon' ? 'warning' : 'info')) ?>"><?= e(ucwords(str_replace('_', ' ', $assignment['deadline_status']))) ?>
</span>
</td>
<td>
<a class="btn btn-sm btn-outline-primary" href="/student/sections/<?= (int) $assignment['section_id'] ?>">View assignment</a>
</td>
</tr>
<?php endforeach; ?>


<?php if (!$list['items']): ?>

<tr>
<td colspan="6" class="text-center text-muted py-4">No matching assignments.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>
