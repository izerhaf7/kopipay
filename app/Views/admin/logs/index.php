<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get" action="<?= site_url('admin/logs') ?>">
            <div class="col-12 col-md-4">
                <label class="form-label">Filter aksi</label>
                <input type="text" class="form-control" name="action" value="<?= esc($action) ?>" placeholder="mis. payroll, employee">
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Cari</button>
            </div>
            <div class="col-6 col-md-2">
                <a class="btn btn-ghost w-100" href="<?= site_url('admin/logs') ?>">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-journal-text me-1"></i> Log Aktivitas</div>
    <div class="kp-table-wrap">
        <table class="table kp-table-sm align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Entitas</th>
                    <th>Detail</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs === []): ?>
                    <tr><td colspan="5" class="kp-empty"><i class="bi bi-inbox"></i> Belum ada log.</td></tr>
                <?php endif ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td style="white-space:nowrap"><?= date('d/m H:i', strtotime($log['created_at'])) ?></td>
                        <td><?= esc($log['username'] ?? 'sistem') ?></td>
                        <td><span class="kp-badge-soft"><?= esc($log['action']) ?></span></td>
                        <td class="kp-muted small"><?= esc($log['entity'] ?? '-') ?><?= ! empty($log['entity_id']) ? ' #' . (int) $log['entity_id'] : '' ?></td>
                        <td class="kp-muted small" style="max-width:420px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"
                            title="<?= esc($log['detail'] ?? '') ?>"><?= esc($log['detail'] ?? '-') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>

<?= $pager->links() ?>
<?= $this->endSection() ?>
