<?php
/**
 * HR Analyzer - لیست دپارتمان‌ها (درختی)
 */
if (!function_exists('hr_render_dept_tree')) {
    function hr_render_dept_tree(array $nodes, int $level = 0): void
    {
        foreach ($nodes as $node) {
            $indent = str_repeat('— ', $level);
            $hasChildren = !empty($node['children']);
            ?>
            <tr>
                <td>
                    <span style="color: #9ca3af;"><?= $indent ?></span>
                    <?php if ($hasChildren): ?>
                        <i class="fas fa-folder-open" style="color: var(--hr-primary);"></i>
                    <?php else: ?>
                        <i class="fas fa-folder" style="color: #9ca3af;"></i>
                    <?php endif; ?>
                    <strong><?= hr_e($node['name']) ?></strong>
                </td>
                <td>
                    <?php if (!empty($node['code'])): ?>
                        <code><?= hr_e($node['code']) ?></code>
                    <?php else: ?>
                        <span class="hr-text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($node['manager_name'])): ?>
                        <?= hr_e($node['manager_name']) ?>
                        <br><small class="hr-text-muted"><?= hr_e($node['manager_code'] ?? '') ?></small>
                    <?php else: ?>
                        <span class="hr-text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td><?= hr_e($node['cost_center'] ?? '—') ?></td>
                <td>
                    <?php if (!empty($node['budget'])): ?>
                        <?= hr_money($node['budget']) ?>
                    <?php else: ?>
                        <span class="hr-text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="hr-status-badge <?= hr_status_class($node['status']) ?>">
                        <?= $node['status'] === 'active' ? 'فعال' : 'غیرفعال' ?>
                    </span>
                </td>
                <td>
                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                        <a href="<?= hr_url('department', 'edit', ['id' => $node['id']]) ?>"
                           class="btn-hr-outline btn-sm" title="ویرایش">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="<?= hr_url('department', 'delete', ['id' => $node['id']]) ?>"
                           class="btn-hr-outline btn-sm hr-confirm-delete"
                           data-message="آیا از حذف دپارتمان '<?= hr_e($node['name']) ?>' اطمینان دارید؟"
                           title="حذف"
                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php
            if ($hasChildren) {
                hr_render_dept_tree($node['children'], $level + 1);
            }
        }
    }
}
?>

<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <!-- هدر -->
    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-sitemap"></i> دپارتمان‌ها
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                ساختار درختی دپارتمان‌ها
                — مجموع: <strong><?= hr_num($stats['total']) ?></strong>
                — فعال: <strong><?= hr_num($stats['active']) ?></strong>
            </p>
        </div>
        <a href="<?= hr_url('department', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> افزودن دپارتمان
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <!-- جدول -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-sitemap"></i>
                ساختار درختی دپارتمان‌ها
            </h3>
        </div>

        <?php if (empty($tree)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-sitemap"></i>
                <h4>هنوز دپارتمانی ثبت نشده است</h4>
                <p>اولین دپارتمان (مثلاً مدیریت منابع انسانی) را ایجاد کنید.</p>
                <a href="<?= hr_url('department', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> ایجاد اولین دپارتمان
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th style="width: 30%;">نام دپارتمان</th>
                            <th>کد</th>
                            <th>مدیر</th>
                            <th>مرکز هزینه</th>
                            <th>بودجه</th>
                            <th>وضعیت</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php hr_render_dept_tree($tree); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>