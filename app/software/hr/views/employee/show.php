<link rel="stylesheet" href="/public/assets/css/modules/hr.css?v=<?= time() ?>">

<div class="software-content hr-fade-in">

    <!-- هدر -->
    <div class="hr-flex-between hr-mb-4">
        <div>
            <h2 style="color: var(--hr-primary-dark); margin: 0;">
                <i class="fas fa-user-tie"></i> <?= hr_e(hr_full_name($employee)) ?>
            </h2>
            <p class="hr-text-muted hr-mt-2" style="margin: 0;">
                کد پرسنلی: <code><?= hr_e($employee['employee_code']) ?></code>
                <span class="hr-status-badge <?= hr_status_class($employee['employment_status']) ?>" style="margin-right: 0.5rem;">
                    <?= hr_employment_status_label($employee['employment_status']) ?>
                </span>
            </p>
        </div>
        <div class="hr-flex hr-gap-2">
            <a href="<?= hr_url('employee', 'edit', ['id' => $employee['id']]) ?>" class="btn-hr-outline">
                <i class="fas fa-edit"></i> ویرایش
            </a>
            <a href="<?= hr_url('employee') ?>" class="btn-hr-outline">
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

    <!-- کارت‌های آماری -->
    <div class="hr-stats-grid hr-mb-4">
        <div class="hr-stat-card">
            <small><i class="fas fa-birthday-cake"></i> سن</small>
            <h3><?= $age !== null ? hr_num($age) . ' سال' : '—' ?></h3>
        </div>
        <div class="hr-stat-card success">
            <small><i class="fas fa-history"></i> سابقه کار</small>
            <h3><?= $tenure !== null ? hr_num($tenure) . ' سال' : '—' ?></h3>
        </div>
        <div class="hr-stat-card">
            <small><i class="fas fa-file-alt"></i> اسناد</small>
            <h3><?= hr_num(count($documents)) ?></h3>
        </div>
        <div class="hr-stat-card warning">
            <small><i class="fas fa-phone"></i> تماس اضطراری</small>
            <h3><?= hr_num(count($contacts)) ?></h3>
        </div>
    </div>

    <!-- گرید -->
    <div class="hr-main-grid">

        <!-- ستون چپ -->
        <div>
            <!-- اطلاعات شناسایی -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-id-card"></i> اطلاعات شناسایی</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">کد پرسنلی</th><td><code><?= hr_e($employee['employee_code']) ?></code></td></tr>
                            <tr><th>کد ملی</th><td><?= hr_e($employee['national_id'] ?? '—') ?></td></tr>
                            <tr><th>نام پدر</th><td><?= hr_e($employee['father_name'] ?? '—') ?></td></tr>
                            <tr><th>جنسیت</th><td><?= hr_gender_label($employee['gender']) ?></td></tr>
                            <tr><th>تاریخ تولد</th><td><?= hr_date($employee['birth_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>محل تولد</th><td><?= hr_e($employee['birth_place'] ?? '—') ?></td></tr>
                            <tr><th>وضعیت تأهل</th><td><?= hr_marital_label($employee['marital_status']) ?></td></tr>
                            <tr><th>تعداد تحت تکفل</th><td><?= hr_num($employee['dependents_count']) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- اطلاعات شغلی -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-briefcase"></i> اطلاعات شغلی</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">دپارتمان</th><td><?= hr_e($employee['department_name'] ?? '—') ?></td></tr>
                            <tr><th>پست</th><td><?= hr_e($employee['position_title'] ?? '—') ?></td></tr>
                            <tr><th>طبقه شغلی</th><td><?= hr_e($employee['grade_code'] ?? '—') ?> — <?= hr_e($employee['grade_name'] ?? '') ?></td></tr>
                            <tr><th>مدیر مستقیم</th><td><?= hr_e($employee['manager_name'] ?? '—') ?></td></tr>
                            <tr><th>تاریخ استخدام</th><td><?= hr_date($employee['hire_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>نوع قرارداد</th><td><?= hr_contract_type_label($employee['contract_type']) ?></td></tr>
                            <tr><th>شروع قرارداد</th><td><?= hr_date($employee['contract_start_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>پایان قرارداد</th><td><?= hr_date($employee['contract_end_date'], 'Y/m/d') ?></td></tr>
                            <tr><th>پایان دوره آزمایشی</th><td><?= hr_date($employee['probation_end_date'], 'Y/m/d') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- اطلاعات تحصیلی -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-graduation-cap"></i> اطلاعات تحصیلی</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th style="width: 180px;">مقطع</th><td><?= hr_education_level_label($employee['education_level']) ?></td></tr>
                            <tr><th>رشته</th><td><?= hr_e($employee['field_of_study'] ?? '—') ?></td></tr>
                            <tr><th>دانشگاه</th><td><?= hr_e($employee['university'] ?? '—') ?></td></tr>
                            <tr><th>سال فارغ‌التحصیلی</th><td><?= hr_e($employee['graduation_year'] ?? '—') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ستون راست -->
        <div>
            <!-- اطلاعات تماس -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-phone"></i> تماس</h3>
                </div>
                <div class="card-body">
                    <table class="hr-table">
                        <tbody>
                            <tr><th>موبایل</th><td><?= hr_e($employee['mobile'] ?? '—') ?></td></tr>
                            <tr><th>تلفن</th><td><?= hr_e($employee['phone'] ?? '—') ?></td></tr>
                            <tr><th>ایمیل</th><td><?= hr_e($employee['email'] ?? '—') ?></td></tr>
                            <tr><th>آدرس</th><td><?= hr_e($employee['address'] ?? '—') ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- اسناد -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> اسناد</h3>
                    <a href="<?= hr_url('employee_document', 'create', ['employee_id' => $employee['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> افزودن
                    </a>
                </div>
                <?php if (empty($documents)): ?>
                    <div class="hr-empty-state" style="padding: 1.5rem;">
                        <i class="fas fa-file"></i>
                        <p>سندی ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($documents as $doc): ?>
                            <?php
                            $ext = $doc['file_name'] ? strtolower(pathinfo($doc['file_name'], PATHINFO_EXTENSION)) : '';
                            $icon = \App\Software\Hr\Helpers\FileUploader::getIcon($ext);
                            $iconColor = \App\Software\Hr\Helpers\FileUploader::getIconColor($ext);
                            ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <div class="hr-flex-between">
                                    <div>
                                        <i class="<?= $icon ?>" style="color: <?= $iconColor ?>;"></i>
                                        <strong><?= hr_e($doc['title']) ?></strong>
                                        <br><small class="hr-text-muted">
                                            <?= hr_e(\App\Software\Hr\Models\EmployeeDocument::getTypeLabel($doc['document_type'])) ?>
                                            — <?= hr_date($doc['issue_date'], 'Y/m/d') ?>
                                        </small>
                                    </div>
                                    <div class="hr-flex hr-gap-2">
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
                                           data-message="حذف سند '<?= hr_e($doc['title']) ?>'؟"
                                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div style="padding: 0.5rem 1rem 1rem; text-align: center;">
                        <a href="<?= hr_url('employee_document', 'index', ['employee_id' => $employee['id']]) ?>"
                           class="btn-hr-outline btn-sm">
                            مشاهده همه اسناد
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- تماس اضطراری -->
            <div class="card hr-mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-phone-alt"></i> تماس اضطراری</h3>
                    <a href="<?= hr_url('employee_contact', 'create', ['employee_id' => $employee['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> افزودن
                    </a>
                </div>
                <?php if (empty($contacts)): ?>
                    <div class="hr-empty-state" style="padding: 1.5rem;">
                        <i class="fas fa-user"></i>
                        <p>مخاطبی ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($contacts as $c): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <div class="hr-flex-between">
                                    <div>
                                        <strong><?= hr_e($c['contact_name']) ?></strong>
                                        <?php if ($c['is_primary']): ?>
                                            <span class="hr-status-badge hr-status-info" style="font-size: 0.65rem;">اصلی</span>
                                        <?php endif; ?>
                                        <br><small class="hr-text-muted">
                                            <?= hr_e($c['relation'] ?? '') ?> —
                                            <?= hr_e($c['mobile'] ?? '') ?>
                                        </small>
                                    </div>
                                    <div class="hr-flex hr-gap-2">
                                        <a href="<?= hr_url('employee_contact', 'edit', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('employee_contact', 'delete', ['id' => $c['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف مخاطب '<?= hr_e($c['contact_name']) ?>'؟"
                                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- تاریخچه -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> تاریخچه</h3>
                    <a href="<?= hr_url('employee_history', 'create', ['employee_id' => $employee['id']]) ?>"
                       class="btn-hr-primary btn-sm">
                        <i class="fas fa-plus"></i> افزودن
                    </a>
                </div>
                <?php if (empty($history)): ?>
                    <div class="hr-empty-state" style="padding: 1.5rem;">
                        <i class="fas fa-history"></i>
                        <p>تاریخچه‌ای ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 1rem;">
                        <?php foreach ($history as $h): ?>
                            <li style="padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb;">
                                <div class="hr-flex-between">
                                    <div>
                                        <span class="hr-status-badge hr-status-info" style="font-size: 0.7rem;">
                                            <?= hr_e(\App\Software\Hr\Models\EmployeeHistory::getTypeLabel($h['change_type'])) ?>
                                        </span>
                                        <small class="hr-text-muted" style="margin-right: 0.5rem;">
                                            <?= hr_date($h['change_date'], 'Y/m/d') ?>
                                        </small>
                                        <?php if (!empty($h['reason'])): ?>
                                            <br><small><?= hr_e($h['reason']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="hr-flex hr-gap-2">
                                        <a href="<?= hr_url('employee_history', 'edit', ['id' => $h['id']]) ?>"
                                           class="btn-hr-outline btn-sm" title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= hr_url('employee_history', 'delete', ['id' => $h['id']]) ?>"
                                           class="btn-hr-outline btn-sm hr-confirm-delete"
                                           data-message="حذف این رکورد تاریخچه؟"
                                           style="color: var(--hr-danger); border-color: var(--hr-danger);">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<script src="/public/assets/js/software/hr.js"></script>