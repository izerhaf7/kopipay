<?php

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */
$pager->setSurroundCount(2);

if ($pager->getPageCount() <= 1) {
    return;
}
?>
<nav aria-label="Navigasi halaman" class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
    <span class="kp-muted small">Halaman <?= $pager->getCurrentPageNumber() ?> dari <?= $pager->getPageCount() ?></span>
    <ul class="pagination mb-0">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Berikutnya">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        <?php endif ?>
    </ul>
</nav>
