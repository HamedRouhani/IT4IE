<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-list"></i> <?= htmlspecialchars($plan['title']) ?></h1>
        <div>
            <a href="/audit-plans" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
            <?php if ($_SESSION['user_role'] === 'admin' || $plan['user_id'] == $_SESSION['user_id']): ?>
                <a href="/audit-plans/edit/<?= $plan['id'] ?>" class="btn btn-warning">
                    <i class="fas fa-edit"></i> ویرایش
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-info-circle"></i> اطلاعات برنامه</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>نوع ممیزی:</strong> <?= $plan['audit_type'] === 'internal' ? 'داخلی' : 'خارجی' ?></p>
                            <p><strong>سرممیز:</strong> <?= htmlspecialchars($plan['lead_auditor_name'] ?? '-') ?></p>
                            <p><strong>تاریخ شروع:</strong> <?= date('Y/m/d', strtotime($plan['start_date'])) ?></p>
                            <p><strong>تاریخ پایان:</strong> <?= date('Y/m/d', strtotime($plan['end_date'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>وضعیت:</strong> 
                                <?php
                                $statusColors = [
                                    'draft' => 'secondary',
                                    'scheduled' => 'info',
                                    'in_progress' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $statusLabels = [
                                    'draft' => 'پیش‌نویس',
                                    'scheduled' => 'زمان‌بندی شده',
                                    'in_progress' => 'در حال انجام',
                                    'completed' => 'تکمیل شده',
                                    'cancelled' => 'لغو شده'
                                ];
                                ?>
                                <span class="badge badge-<?= $statusColors[$plan['status']] ?>">
                                    <?= $statusLabels[$plan['status']] ?>
                                </span>
                            </p>
                            <p><strong>معیارها:</strong> <?= htmlspecialchars($plan['criteria'] ?? '-') ?></p>
                        </div>
                    </div>

                    <?php if (!empty($plan['scope'])): ?>
                        <hr>
                        <h6><i class="fas fa-bullseye"></i> دامنه ممیزی</h6>
                        <p><?= nl2br(htmlspecialchars($plan['scope'])) ?></p>
                    <?php endif; ?>

                    <?php if (!empty($plan['objectives'])): ?>
                        <h6><i class="fas fa-flag"></i> اهداف ممیزی</h6>
                        <p><?= nl2br(htmlspecialchars($plan['objectives'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- آمار -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-chart-bar"></i> آمار برنامه</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-primary"><?= $statistics['sessions']['total'] ?? 0 ?></h3>
                                <p class="mb-0">جلسات</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-success"><?= $statistics['sessions']['completed'] ?? 0 ?></h3>
                                <p class="mb-0">تکمیل شده</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-info"><?= $statistics['evidences']['total'] ?? 0 ?></h3>
                                <p class="mb-0">شواهد</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h3 class="text-danger"><?= ($statistics['evidences']['minor_nc'] ?? 0) + ($statistics['evidences']['major_nc'] ?? 0) ?></h3>
                                <p class="mb-0">عدم انطباق</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- تغییر وضعیت -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-exchange-alt"></i> تغییر وضعیت</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/audit-plans/change-status/<?= $plan['id'] ?>">
                        <select class="form-control mb-3" name="status">
                            <option value="draft" <?= $plan['status'] === 'draft' ? 'selected' : '' ?>>پیش‌نویس</option>
                            <option value="scheduled" <?= $plan['status'] === 'scheduled' ? 'selected' : '' ?>>زمان‌بندی شده</option>
                            <option value="in_progress" <?= $plan['status'] === 'in_progress' ? 'selected' : '' ?>>در حال انجام</option>
                            <option value="completed" <?= $plan['status'] === 'completed' ? 'selected' : '' ?>>تکمیل شده</option>
                            <option value="cancelled" <?= $plan['status'] === 'cancelled' ? 'selected' : '' ?>>لغو شده</option>
                        </select>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-check"></i> اعمال تغییرات
                        </button>
                    </form>
                </div>
            </div>

            <!-- جلسات -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar"></i> جلسات</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($sessions)): ?>
                        <p class="text-muted text-center">هنوز جلسه‌ای ثبت نشده است</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($sessions as $session): ?>
                                <a href="/audit-sessions/view/<?= $session['id'] ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1"><?= htmlspecialchars($session['department_name'] ?? 'نامشخص') ?></h6>
                                        <small><?= date('Y/m/d', strtotime($session['actual_date'])) ?></small>
                                    </div>
                                    <p class="mb-1 small"><?= htmlspecialchars($session['process_name'] ?? '-') ?></p>
                                    <small class="text-muted"><?= htmlspecialchars($session['auditor_name'] ?? '-') ?></small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>