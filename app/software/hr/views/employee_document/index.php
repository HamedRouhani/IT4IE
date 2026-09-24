<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-file-alt"></i> اسناد پرسنلی
            </h2>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('employee_document', 'create', $employee ? ['employee_id' => $employee['id']] : []) ?>"
               class="btn-hr-primary">
                <i class="fas fa-plus"></i> افزودن سند
            </a>
            <a href="<?= $employee ? hr_url('employee', 'show', ['id' => $employee['id']]) : hr_url('employee') ?>"
               class="btn-hr-outline">
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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-list"></i> لیست اسناد</h3>
        </div>

        <?php if (empty($documents)): ?>
            <div class="hr-empty-state">
                <i class="fas fa-file"></i>
                <h4>هیچ سندی ثبت نشده است</h4>
                <p>اولین سند پرسنلی را اضافه کنید.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="hr-table">
                    <thead>
                        <tr>
                            <th>نوع</th>
                            <th>عنوان</th>
                            <?php if (!$employee): ?><th>کارمند</th><?php endif; ?>
                            <th>فایل</th>
                            <th>تاریخ صدور</th>
                            <th>تاریخ انقضا</th>
                            <th>وضعیت</th>
                            <th style="width: 180px; text-align: center;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                            <?php
                            $isExpired = $doc['expiry_date'] && $doc['expiry_date'] < date('Y-m-d');
                            $isExpiringSoon = $doc['expiry_date']
                                && $doc['expiry_date'] >= date('Y-m-d')
                                && $doc['expiry_date'] <= date('Y-m-d', strtotime('+60 days'));
                            $ext = $doc['file_name'] ? strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION)) : '';
                            $icon = \App\Software\Hr\Helpers\FileUploader::getIcon($ext);
                            $iconColor = \App\Software\Hr\Helpers\FileUploader::getIconColor($ext);
                            ?>
                            <tr>
                                <td>
                                    <span class="hr-status-badge hr-status-info">
                                        <?= hr_e(\App\Software\Hr\Models\EmployeeDocument::getTypeLabel($doc['document_type'])) ?>
                                    </span>
                                </td>
                                <td><strong><?= hr_e($doc['title']) ?></strong></td>
                                <?php if (!$employee): ?>
                                    <td><?= hr_e($doc['employee_name'] ?? '—') ?></td>
                                <?php endif; ?>
                                <td>
                                    <?php if (!empty($doc['file_name'])): ?>
                                        <i class="<?= $icon ?>" style="color: <?= $iconColor ?>;"></i>
                                        <small><?= hr_e(hr_truncate($doc['file_name'], 25)) ?></small>
                                    <?php else: ?>
                                        <span class="hr-text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= hr_date($doc['issue_date'], 'Y/m/d') ?></td>
                                <td><?= hr_date($doc['expiry_date'], 'Y/m/d') ?></td>
                                <td>
                                    <?php if ($isExpired): ?>
                                        <span class="hr-status-badge hr-status-danger">منقضی</span>
                                    <?php elseif ($isExpiringSoon): ?>
                                        <span class="hr-status-badge hr-status-warning">نزدیک انقضا</span>
                                    <?php else: ?>
                                        <span class="hr-status-badge hr-status-active">معتبر</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="hr-flex hr-gap-2" style="justify-content: center;">
                                        <?php if (!empty($doc['file_path'])): ?>
                                            <a href="<?= hr_url('employee_document', 'download', ['id' => $doc['id']]) ?>"
                                               class="btn-hr-outline btn-sm" title="دانلود">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= hr_url('employee_document', 'edit', ['id' => $doc['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('employee_document', 'delete', ['id' => $doc['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="آیا از حذف سند '<?= hr_e($doc['title']) ?>' و فایل مربوطه اطمینان دارید؟"
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