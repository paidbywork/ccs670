<?php
$sortOptions = ['newest' => 'Recently enrolled', 'oldest' => 'First enrolled', 'course' => 'Course code', 'name' => 'Section name'];
$extraOptions = [];
require dirname(__DIR__) . '/partials/list_filters.php';
?>
<div class="card">
<div class="card-body">
<h2 class="h5">My enrolled sections (<?= $list['total'] ?>)</h2>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead>
<tr>
<th>Course</th>
<th>Section</th>
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
<td>
<a class="btn btn-sm btn-outline-primary" href="/student/sections/<?= (int) $section['id'] ?>">View coursework</a>
</td>
</tr>
<?php endforeach; ?>


<?php if (!$list['items']): ?>

<tr>
<td colspan="3" class="text-center text-muted py-4">No matching enrollments. <a href="/student/join">Join a section</a>.</td>
</tr>
<?php endif; ?>

</tbody>
</table>
</div><?php require dirname(__DIR__) . '/partials/list_pagination.php'; ?>
</div>
</div>
