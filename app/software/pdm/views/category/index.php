<?php
/**
 * PdM Analyzer - لیست دسته‌بندی دارایی‌ها (درختی)
 * مسیر: app/software/pdm/views/category/index.php
 */

if (!function_exists('pdm_render_category_tree')) {
    function pdm_render_category_tree(array $nodes, int $level = 0): void
    {
        foreach ($nodes as $node) {
            $indent = str_repeat('— ', $level);
            $hasChildren = !empty($node['children']);
            ?>
            <tr>
                <td>
                    <span style="color: #9ca3af;"><?= $indent ?></span>
                    <?php if ($hasChildren): ?>
                        <i class="fas fa-folder-open" style="color: var(--pdm-primary);"></i>
                    <?php else: ?>
                        <i class="fas fa-tag" style="color: #9ca3af;"></i>
                    <?php endif; ?>
                    <strong><?= pdm_e($node['name']) ?></strong>
                </td>
                <td><?= pdm_e(pdm_truncate($node['description'] ?? '—', 60)) ?></td>
                <td class="pdm-text-center">
                    <div class="pdm-flex pdm-gap-2" style="justify-content: center;">
                        <a href="<?= pdm_url('asset_category', 'edit', ['id' => $node['id']]) ?>"
                           class="btn-pdm-outline btn-sm"
                           title="ویرایش">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= pdm_url('asset_category', 'delete', ['id' => $node['id']]) ?>"
                           class="btn-pdm-outline btn-sm pdm-confirm-delete"
                           data-message="آیا از حذف دسته '<?= pdm_e($node['name']) ?>' اطمینان دارید؟"
                           title="حذف"
                           style="color: var(--pdm-danger); border-color: var(--pdm-danger);">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            if ($hasChildren) {
                pdm_render_category_tree($node['children'], $level + 1);
            }
        }
    }
}
?>

<link rel="stylesheet" href="/public/assets/css/modules/pdm.css?v=<?= time() ?>">

<div class="software-content pdm-fade-in">

    <div class="pdm-flex-between pdm-mb-4">
        <div>
            <h2 style="color: var(--pdm-primary-dark); margin: 0;">
                <i class="fas fa-tags"></i> دسته‌بندی دارایی‌ها
            </h2>
            <p class="pdm-text-muted pdm-mt-2" style="margin: 0;">
                ساختار درختی دسته‌بندی تجهیزات
                — مجموع: <strong><?= pdm_num($totalCount) ?></strong> دسته
            </p>
        </div>
        <a href="<?= pdm_url('asset_category', 'create') ?>" class="btn-pdm-primary">
            <i class="fas fa-plus"></i> افزودن دسته
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="pdm-alert <?= pdm_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= pdm_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sitemap"></i>
                ساختار درختی دسته‌بندی‌ها
            </h3>
        </div>

        <?php if (empty($tree)): ?>
            <div class="pdm-empty-state">
                <i class="fas fa-tags"></i>
                <h4>هنوز دسته‌بندی ثبت نشده است</h4>
                <p>اولین دسته (مثلاً پمپ‌ها) را ایجاد کنید.</p>
                <a href="<?= pdm_url('asset_category', 'create') ?>" class="btn-pdm-primary">
                    <i class="fas fa-plus"></i> ایجاد اولین دسته
                </a>
            </div>
        <?php else: ?>
            <table class="pdm-table">
                <thead>
                    <tr>
                        <th style="width: 45%;">نام دسته</th>
                        <th>توضیحات</th>
                        <th style="width: 150px; text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php pdm_render_category_tree($tree); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/pdm.js"></script>