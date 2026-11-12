<?= $this->extend('layouts/auth') ?>

<?= $this->section('content') ?>
<form action="<?= site_url('login') ?>" method="post" autocomplete="off">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label" for="login">Username atau Email</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0" style="border-color:var(--kp-border)"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control border-start-0" id="login" name="login"
                   value="<?= esc(old('login')) ?>" placeholder="KP-001 atau admin" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0" style="border-color:var(--kp-border)"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="••••••••" required>
            <button class="btn btn-ghost" type="button" id="togglePass" tabindex="-1" aria-label="Lihat password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
    </div>

    <button class="btn btn-primary w-100 py-2 mt-2" type="submit">
        <i class="bi bi-cup-hot me-1"></i> Masuk
    </button>
</form>

<p class="text-center kp-muted mt-3 mb-0" style="font-size:.75rem">
    Akun dibuat oleh Admin. Lupa password? Hubungi Admin kedai.
</p>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.getElementById('togglePass')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
        this.querySelector('i').className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    });
</script>
<?= $this->endSection() ?>
