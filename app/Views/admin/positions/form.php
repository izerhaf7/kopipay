<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php $isEdit = $position !== null; ?>
<div class="row">
    <div class="col-12 col-lg-7">
        <div class="card">
            <div class="card-header"><?= $isEdit ? 'Edit Jabatan' : 'Jabatan Baru' ?></div>
            <div class="card-body">
                <form action="<?= $isEdit ? site_url('admin/positions/' . $position['id']) : site_url('admin/positions') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="name">Nama Jabatan</label>
                        <input type="text" class="form-control" id="name" name="name" required maxlength="100"
                               value="<?= esc(old('name', $position['name'] ?? '')) ?>" placeholder="Barista">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="daily_rate">Tarif Harian (Rp)</label>
                        <input type="text" class="form-control kp-money-input" id="daily_rate" name="daily_rate" required
                               inputmode="numeric" value="<?= esc(old('daily_rate', $position['daily_rate'] ?? '')) ?>" placeholder="100000">
                        <div class="form-text">Dibayarkan untuk setiap hari hadir. Tanpa desimal.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Keterangan</label>
                        <input type="text" class="form-control" id="description" name="description" maxlength="255"
                               value="<?= esc(old('description', $position['description'] ?? '')) ?>" placeholder="Opsional">
                    </div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah Jabatan' ?></button>
                        <a class="btn btn-ghost" href="<?= site_url('admin/positions') ?>">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.kp-money-input').forEach(function (input) {
    const format = () => {
        const digits = input.value.replace(/\D/g, '');
        input.value = digits === '' ? '' : new Intl.NumberFormat('id-ID').format(digits);
    };
    input.addEventListener('input', format);
    format();
});
</script>
<?= $this->endSection() ?>
