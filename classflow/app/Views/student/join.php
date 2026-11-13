<div class="card">
<div class="card-body">
    <h2 class="h5">Enroll using a section code</h2>
    <p class="text-muted">Ask your teacher or administrator for the section enrollment code.</p>
    <form method="post" action="/student/enroll" class="row g-3">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div class="col-md-8">
<label for="enrollment-code" class="form-label">Section enrollment code</label>
<input id="enrollment-code" name="enrollment_code" maxlength="32" required class="form-control">
</div>
        <div class="col-md-4 d-flex align-items-end">
<button class="btn btn-primary" type="submit">Join section</button>
</div>
    </form>
</div>
</div>
