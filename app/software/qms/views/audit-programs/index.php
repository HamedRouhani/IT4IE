<?php
$statusLabels = ['draft'=>'پیش‌نویس','approved'=>'تأیید شده','active'=>'فعال','completed'=>'تکمیل شده','archived'=>'بایگانی'];
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-calendar-alt me-2"></i>برنامه‌های سالانه ممیزی</h4>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>برنامه جدید
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>عنوان</th><th>سال</th><th>وضعیت</th>
                        <th class="text-center">ارزیابی ریسک</th><th class="text-end">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($programs)): ?>
                    <tr><td colspan="5" class="text-center text-muted py-5">هنوز برنامه‌ای ایجاد نشده است.</td></tr>
                <?php else: ?>
                    <?php foreach ($programs as $p): ?>
                    <tr>
                        <td><strong><?= qms_e($p['title']) ?></strong></td>
                        <td><?= fa_digits($p['year']) ?></td>
                        <td><?= $statusLabels[$p['status']] ?? $p['status'] ?></td>
                        <td class="text-center"><?= fa_digits($p['risk_count']) ?></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=show&id=<?= $p['id'] ?>">مشاهده</a>
                            <a class="btn btn-sm btn-outline-warning" href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=riskAssessment&id=<?= $p['id'] ?>">ارزیابی ریسک</a>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= CURRENT_MODULE_URL ?>?controller=auditprograms&action=edit&id=<?= $p['id'] ?>">ویرایش</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>