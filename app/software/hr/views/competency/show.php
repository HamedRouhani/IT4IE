<?php
use App\Software\Hr\Models\Competency;
?>
<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-cube"></i> <?= hr_e($competency['name']) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                <?php if (!empty($competency['code'])): ?>
                    کد: <code><?= hr_e($competency['code']) ?></code> —
                <?php endif; ?>
                <span class="hr-status-badge <?= Competency::getCategoryClass($competency['category']) ?>">
                    <?= hr_e($categoryOptions[$competency['category']] ?? '') ?>
                </span>
                <span class="hr-status-badge <?= hr_status_class($competency['status']) ?>">
                    <?= hr_e($statusOptions[$competency['status']] ?? '') ?>
                </span>
                <?php if (!empty($competency['is_core'])): ?>
                    <span class="hr-status-badge hr-status-danger">
                        <i class="fas fa-star"></i> هسته‌ای
                    </span>
                <?php endif; ?>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('competency', 'edit', ['id' => $competency['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('competency') ?>" class="btn-hr-outline">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="hr-main-grid">

        <div>
            <div class="card hr-mb-3">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> اطلاعات شایستگی</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">نام</th><td><?= hr_e($competency['name']) ?></td></tr>
                            <tr><th>کد</th><td><?= hr_e($competency['code'] ?? '—') ?></td></tr>
                            <tr><th>والد</th><td>
                                <?php if (!empty($competency['parent_id'])): ?>
                                    <a href="<?= hr_url('competency', 'show', ['id' => $competency['parent_id']]) ?>">
                                        <?= hr_e($competency['parent_name'] ?? '') ?>
                                    </a>
                                <?php else: ?>—<?php endif; ?>
                            </td></tr>
                            <tr><th>دسته‌بندی</th><td><?= hr_e($categoryOptions[$competency['category']] ?? '') ?></td></tr>
                            <tr><th>نوع</th><td><?= hr_e($typeOptions[$competency['competency_type']] ?? '') ?></td></tr>
                            <tr><th>ترتیب</th><td><?= hr_num($competency['sort_order'] ?? 0) ?></td></tr>
                            <tr><th>هسته‌ای</th><td>
                                <?= !empty($competency['is_core']) ? 'بله' : 'خیر' ?>
                            </td></tr>
                            <?php if (!empty($competency['description'])): ?>
                                <tr><th>توضیحات</th><td><?= nl2br(hr_e($competency['description'])) ?></td></tr>
                            <?php endif; ?>
                            <?php if (!empty($competency['levels'])): ?>
                                <tr><th>سطوح</th><td>
                                    <pre style="direction:ltr;text-align:left;background:#f8fafc;padding:0.75rem;border-radius:4px;font-size:0.85rem;"><?= hr_e($competency['levels']) ?></pre>
                                </td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if (!empty($children)): ?>
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sitemap"></i> زیرشاخه‌ها (<?= hr_num(count($children)) ?>)
                        </h3>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="hr-table">
                            <thead>
                                <tr>
                                    <th>نام</th>
                                    <th>کد</th>
                                    <th>دسته‌بندی</th>
                                    <th>وضعیت</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($children as $c): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= hr_url('competency', 'show', ['id' => $c['id']]) ?>"
                                               style="color: var(--hr-primary-dark); font-weight: 600;">
                                                <?= hr_e($c['name']) ?>
                                            </a>
                                        </td>
                                        <td><code><?= hr_e($c['code'] ?? '—') ?></code></td>
                                        <td>
                                            <span class="hr-status-badge <?= Competency::getCategoryClass($c['category']) ?>">
                                                <?= hr_e($categoryOptions[$c['category']] ?? '') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="hr-status-badge <?= hr_status_class($c['status']) ?>">
                                                <?= hr_e($statusOptions[$c['status']] ?? '') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="card">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie"></i> خلاصه</h3></div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr>
                                <th>وضعیت</th>
                                <td>
                                    <span class="hr-status-badge <?= hr_status_class($competency['status']) ?>">
                                        <?= hr_e($statusOptions[$competency['status']] ?? '') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>تعداد زیرشاخه</th>
                                <td><strong><?= hr_num(count($children)) ?></strong></td>
                            </tr>
                            <tr>
                                <th>تاریخ ایجاد</th>
                                <td><?= hr_date($competency['created_at'] ?? null, 'Y/m/d H:i') ?></td>
                            </tr>
                            <tr>
                                <th>آخرین به‌روزرسانی</th>
                                <td><?= hr_date($competency['updated_at'] ?? null, 'Y/m/d H:i') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>