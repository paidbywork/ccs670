<?php
/** Required: $filters, $sortOptions. Optional: $extraOptions. */
$extraOptions = $extraOptions ?? [];
?>
<form method="get" class="card mb-3" role="search">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label for="list-search" class="form-label">Search</label>
                <input id="list-search" type="search" name="q" maxlength="100" class="form-control"
                    value="<?= e($filters['q']) ?>" placeholder="Search records">
            </div>
            
<?php foreach ($extraOptions as $name => $config): ?>

                <div class="col-md-2">
                    <label for="filter-<?= e($name) ?>" class="form-label"><?= e($config['label']) ?>
</label>
                    <select id="filter-<?= e($name) ?>" name="<?= e($name) ?>" class="form-select">
                        
<?php foreach ($config['values'] as $value => $label): ?>

                            <option value="<?= e((string) $value) ?>" <?= $config['selected'] === (string) $value ? 'selected' : '' ?>><?= e($label) ?>
</option>
                        
<?php endforeach; ?>

                    </select>
                </div>
            
<?php endforeach; ?>

            <div class="col-md-2">
                <label for="list-sort" class="form-label">Sort by</label>
                <select id="list-sort" name="sort" class="form-select">
                    
<?php foreach ($sortOptions as $key => $label): ?>

                        <option value="<?= e($key) ?>" <?= $filters['sort'] === $key ? 'selected' : '' ?>><?= e($label) ?>
</option>
                    
<?php endforeach; ?>

                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button class="btn btn-primary" type="submit">Apply</button>
                <a class="btn btn-outline-secondary" href="<?= e(strtok($_SERVER['REQUEST_URI'], '?')) ?>">Reset</a>
            </div>
        </div>
    </div>
</form>
