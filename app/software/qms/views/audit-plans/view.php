<?php
/**
 * ویو: جزئیات برنامه ممیزی
 * متغیرهای ورودی از کنترلر: $plan, $sessions, $statistics
 */

$statusLabels = ['draft'=>'پیش‌نویس','scheduled'=>'زمان‌بندی شده','in_progress'=>'در حال اجرا','completed'=>'تکمیل شده','cancelled'=>'لغو شده'];
$statusColors = ['draft'=>'secondary','scheduled'=>'info','in_progress'=>'primary','completed'=>'success','cancelled'=>'danger'];
$typeLabels   = ['internal'=>'ممیزی داخلی','external'=>'ممیزی خارجی','surveillance'=>'ممیزی مراقبتی','recertification'=>'ممیزی تمدید','special'=>'ممیزی ویژه'];
$priorityLabels = ['low'=>'کم','medium'=>'متوسط','high'=>'زیاد','critical'=>'بحرانی'];
$sesLabels = ['not_started'=>'شروع نشده','in_progress'=>'در حال اجرا','completed'=>'تکمیل شده','postponed'=>'به تعویق افتاده','cancelled'=>'لغو شده'];
$sesColors = ['not_started'=>'secondary','in_progress'=>'primary','completed'=>'success','postponed'=>'warning','cancelled'=>'danger'];
?>
<div class="container-fluid py-4">

    <!-- سربرگ -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">
                <i class="fas fa-clipboard-list me-2 text-primary"></i><?= htmlspecialchars($plan['title']) ?>
                <span class="badge bg-<?= $statusColors[$plan['status']] ?? 'secondary' ?> align-middle ms-2"><?= $statusLabels[$plan['status']] ?? $plan['status'] ?></span>
            </h4>
            <p class="text-muted small mb-0"><?= $typeLabels[$plan['audit_type']] ?? $plan['audit_type'] ?> | اولویت: <?= $priorityLabels[$plan['priority']] ?? $plan['priority'] ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=team&id=<?= $plan['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-users me-1"></i>تیم ممیزی
            </a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=edit&id=<?= $plan['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-edit me-1"></i>ویرایش
            </a>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditplans" class="btn btn-outline-secondary">بازگشت</a>
        </div>
    </div>

    <!-- اطلاعات برنامه -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">اطلاعات برنامه ممیزی</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <small class="text-muted d-block">سرممیز</small>
                    <strong>
                        <?php if (!empty($plan['lead_auditor_name'])): ?>
                            <span class="badge bg-primary"><i class="fas fa-star me-1"></i><?= htmlspecialchars($plan['lead_auditor_name']) ?></span>
                        <?php else: ?>
                            <span class="text-danger">تعیین نشده</span>
                        <?php endif; ?>
                    </strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">تاریخ شروع</small>
                    <strong><?= htmlspecialchars($plan['start_date'] ?? '-') ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">تاریخ پایان</small>
                    <strong><?= htmlspecialchars($plan['end_date'] ?? '-') ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">معیار ممیزی</small>
                    <strong><?= htmlspecialchars($plan['criteria'] ?: 'ISO 9001:2015') ?></strong>
                </div>
                <?php if (!empty($plan['scope'])): ?>
                <div class="col-md-6">
                    <small class="text-muted d-block">دامنه کاربرد</small>
                    <?= htmlspecialchars($plan['scope']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($plan['objectives'])): ?>
                <div class="col-md-6">
                    <small class="text-muted d-block">اهداف ممیزی</small>
                    <?= htmlspecialchars($plan['objectives']) ?>
                </div>
                <?php endif; ?>
            </div>
            <hr>
            <!-- تغییر وضعیت -->
            <form method="POST" action="<?= CURRENT_MODULE_URL ?>?controller=auditplans&action=changeStatus&id=<?= $plan['id'] ?>" class="row g-2 align-items-center">
                <div class="col-auto"><small class="text-muted">تغییر وضعیت:</small></div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $plan['status'] === $key ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">اعمال</button>
                </div>
            </form>
        </div>
    </div>

    <!-- آمار -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <h5 class="mb-0 text-primary"><?= (int)($statistics['sessions']['total'] ?? 0) ?></h5>
                    <small class="text-muted">جلسات ممیزی</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <h5 class="mb-0 text-success"><?= (int)($statistics['sessions']['completed'] ?? 0) ?></h5>
                    <small class="text-muted">تکمیل شده</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <h5 class="mb-0 text-warning"><?= (int)($statistics['evidences']['minor_nc'] ?? 0) ?></h5>
                    <small class="text-muted">عدم انطباق جزئی</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card shadow-sm text-center">
                <div class="card-body py-3">
                    <h5 class="mb-0 text-danger"><?= (int)($statistics['evidences']['major_nc'] ?? 0) ?></h5>
                    <small class="text-muted">عدم انطباق عمده</small>
                </div>
            </div>
        </div>
    </div>

    <!-- جلسات ممیزی -->
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span><i class="fas fa-calendar-alt me-2"></i>جلسات ممیزی</span>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=auditsessions" class="btn btn-sm btn-outline-primary">مدیریت جلسات</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>واحد سازمانی</th>
                        <th>فرآیند</th>
                        <th>تاریخ</th>
                        <th>ممیز</th>
                        <th>وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sessions)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">هنوز جلسه‌ای برای این برنامه ثبت نشده است.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td><?= $s['session_number'] ?></td>
                            <td><?= htmlspecialchars($s['department_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['process_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['audit_date'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($s['auditor_name'] ?? '-') ?></td>
                            <td><span class="badge bg-<?= $sesColors[$s['overall_status']] ?? 'secondary' ?>"><?= $sesLabels[$s['overall_status']] ?? $s['overall_status'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>