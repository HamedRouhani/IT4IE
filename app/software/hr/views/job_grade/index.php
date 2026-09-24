<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-layer-group"></i> طبقه‌بندی شغلی
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                مجموع: <strong><?= hr_num($stats['total']) ?></strong> طبقه
                <?php if ($stats['min_level'] && $stats['max_level']): ?>
                    — سطوح: <strong><?= hr_num($stats['min_level']) ?></strong> تا <strong><?= hr_num($stats['max_level']) ?></strong>
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= hr_url('job_grade', 'create') ?>" class="btn-hr-primary">
            <i class="fas fa-plus"></i> افزودن طبقه
        </a>
    </div>

    <?php if (!empty($flash)): ?>
        <div class="hr-alert <?= hr_e($flash['type']) ?>">
            <i class="fas fa-info-circle"></i>
            <?= hr_e($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> لیست طبقات شغلی</h3>
        </div>

        <?php if (empty($grades)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-layer-group"></i>
                <h4>هنوز طبقه‌ای ثبت نشده است</h4>
                <p>اولین طبقه شغلی (مثلاً G1) را ایجاد کنید.</p>
                <a href="<?= hr_url('job_grade', 'create') ?>" class="btn-hr-primary">
                    <i class="fas fa-plus"></i> افزودن طبقه
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>سطح</th>
                            <th>کد</th>
                            <th>نام</th>
                            <th>حداقل حقوق</th>
                            <th>حداکثر حقوق</th>
                            <th>تعداد پست</th>
                            <th>تعداد کارمند</th>
                            <th style="width: 120px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grades as $g): ?>
                            <tr>
                                <td>
                                    <span class="hr-status-badge hr-status-info">
                                        سطح <?= hr_num($g['level']) ?>
                                    </span>
                                </td>
                                <td><code><?= hr_e($g['code']) ?></code></td>
                                <td><strong><?= hr_e($g['name']) ?></strong></td>
                                <td><?= !empty($g['min_salary']) ? hr_money($g['min_salary']) : '—' ?></td>
                                <td><?= !empty($g['max_salary']) ? hr_money($g['max_salary']) : '—' ?></td>
                                <td><?= hr_num($g['positions_count']) ?></td>
                                <td><?= hr_num($g['employees_count']) ?></td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <a href="<?= hr_url('job_grade', 'edit', ['id' => $g['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('job_grade', 'delete', ['id' => $g['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="آیا از حذف طبقه '<?= hr_e($g['name']) ?>' اطمینان دارید؟"
                                           title="حذف"
                                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
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

<script src="/public/assets/js/software/hr.js"></script>