<?php
/** Required: $list. Retains the current validated query parameters. */
$first = $list['total'] === 0 ? 0 : (($list['page'] - 1) * $list['perPage'] + 1);
$last = min($list['total'], $list['page'] * $list['perPage']);
$query = $_GET;
$path = strtok($_SERVER['REQUEST_URI'], '?');
?>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
    <p class="text-muted small mb-0">Showing <?= $first ?>–<?= $last ?> of <?= $list['total'] ?> records</p>
    
<?php if ($list['pages'] > 1): ?>

        <nav aria-label="List pagination">
<ul class="pagination mb-0">
            
<?php foreach (array_unique(array_filter([1, $list['page'] - 1, $list['page'], $list['page'] + 1, $list['pages']], fn ($n) => $n >= 1 && $n <= $list['pages'])) as $pageNumber): ?>

                <?php $query['page'] = $pageNumber; ?>
                <li class="page-item <?= $pageNumber === $list['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e($path . '?' . http_build_query($query)) ?>" <?= $pageNumber === $list['page'] ? 'aria-current="page"' : '' ?>><?= $pageNumber ?>
</a>
                </li>
            
<?php endforeach; ?>

        </ul>
</nav>
    
<?php endif; ?>

</div>
