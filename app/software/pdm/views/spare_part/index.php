<?php
/**
 * PdM Analyzer - لیست قطعات یدکی
 * مسیر: app/software/pdm/views/spare_part/index.php
 */
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <!-- هدر -->
    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-puzzle-piece"></i> قطعات یدکی
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                مجموع: <strong><?= pdm_num($stats['total']) ?></strong> قطعه
                — کل موجودی: <strong><?= pdm_num($stats['total_quantity']) ?></strong>
                <?php if ($stats['low_stock'] > 0): ?>
                    — <strong style="color: var(--pdm-danger);">
                        <?= pdm_num($stats['low_stock']) ?> قطعه زیر حد مجاز
                    </strong>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= pdm_url('spare_part', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> افزودن قطعه
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- فیلتر -->
    <div class="card pdm-mb-3">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                فیلتر و جستجو
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="<?= pdm_url('spare_part') ?>">
                <input type="hidden" name="controller" value="spare_part">
                <input type="hidden" name="action" value="index">

                <div class="pdm-form-row">
                    <div class="pdm-form-group">
                        <label class="pdm-form-label">جستجو</label>
                        <input type="text" name="q" class="pdm-form-control"
                               placeholder="کد، نام یا سازنده..."
                               value="<?= pdm_e($filters['q']) ?>">
                    </div>

                    <div class="pdm-form-group" style="display: flex; align-items: flex-end;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="low_stock" value="1"
                                <?= $filters['low_stock'] ? 'checked' : '' ?>>
                            <span>فقط قطعاتی که موجودی کم دارند</span>
                        </label>
                    </div>

                    <div class="pdm-form-group" style="display: flex; align-items: flex-end;">
                        <div class="pdm-flex pdm-gap-2">
                            <button type="submit" class="btn-pdm-primary">
                                <i class="fas fa-search"></i> اعمال
                            </button>
                            <a href="<?= pdm_url('spare_part') ?>" class="btn-pdm-outline">
                                <i class="fas fa-redo"></i> پاک کردن
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i>
                لیست قطعات
                <span class="pdm-text-muted" style="font-weight: normal; font-size: 0.85rem;">
                    (<?= pdm_num(count($spareParts)) ?> مورد)
                </span>
            </h3>
        </div>

        <?php if (empty($spareParts)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-puzzle-piece"></i>
                <h4>هیچ قطعه‌ای ثبت نشده است</h4>
                <p>اولین قطعه یدکی خود را اضافه کنید.</p>
                <a href="<?= pdm_url('spare_part', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> افزودن قطعه
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="pdm-table">
                    <thead>
                        <tr>
                            <th>کد</th>
                            <th>نام</th>
                            <th>سازنده</th>
                            <th>موجودی</th>
                            <th>حد مجاز</th>
                            <th>واحد</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($spareParts as $sp): ?>
                            <?php $isLow = (int) $sp['stock_quantity'] <= (int) $sp['minimum_stock']; ?>
                            <tr style="<?= $isLow ? 'background: #fef3c7;' : '' ?>">
                                <td><code><?= pdm_e($sp['code']) ?></code></td>
                                <td><strong><?= pdm_e($sp['name']) ?></strong></td>
                                <td><?= pdm_e($sp['manufacturer'] ?? '—') ?></td>
                                <td>
                                    <strong style="<?= $isLow ? 'color: var(--pdm-danger);' : '' ?>">
                                        <?= pdm_num($sp['stock_quantity']) ?>
                                    </strong>
                                </td>
                                <td><?= pdm_num($sp['minimum_stock']) ?></td>
                                <td><?= pdm_e($sp['unit'] ?? 'عدد') ?></td>
                                <td>
                                    <?php if ($isLow): ?>
                                        <span class="pdm-criticality-badge pdm-criticality-critical">
                                            کمبود
                                        </span>
                                    <?php else: ?>
                                        <span class="pdm-criticality-badge pdm-criticality-low">
                                            کافی
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                                        <a href="<?= pdm_url('spare_part', 'edit', ['id' => $sp['id']]) ?>"
                                           class="btn-pdm-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= pdm_url('spare_part', 'delete', ['id' => $sp['id']]) ?>"
                                           class="btn-pdm-outline btn-sm pdm-confirm-delete"
                                           data-message="آیا از حذف قطعه '<?= pdm_e($sp['name']) ?>' اطمینان دارید؟"
                                           title="حذف"
                                           style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>