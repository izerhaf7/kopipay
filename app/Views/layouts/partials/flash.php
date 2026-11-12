<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success d-flex align-items-start gap-2 border-0" style="background:#E7EFE7;color:#2F5730">
        <i class="bi bi-check-circle-fill mt-1"></i>
        <div><?= esc(session()->getFlashdata('message')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger d-flex align-items-start gap-2 border-0" style="background:#F7E5E5;color:#8C2A2A">
        <i class="bi bi-exclamation-triangle-fill mt-1"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger border-0" style="background:#F7E5E5;color:#8C2A2A">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Periksa kembali isian Anda:</div>
        <ul class="mb-0 ps-3">
            <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (session()->has('warning')): ?>
    <div class="alert border-0" style="background:#FBF1DE;color:#7A5405">
        <i class="bi bi-info-circle-fill me-1"></i> <?= esc(session('warning')) ?>
    </div>
<?php endif; ?>
