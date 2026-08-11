<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-clipboard-list"></i> برنامه‌های ممیزی</h1>
        <a href="/audit-plans/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> برنامه جدید
        </a>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['message']) ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <?php if (empty($plans)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">هیچ برنامه ممیزی ثبت نشده است</h4>
                    <a href="/audit-plans/create" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> ایجاد اولین برنامه
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>عنوان</th>
                                <th>نوع</th>
                                <th>سرممیز</th>
                                <th>تاریخ شروع</th>
                                <th>تاریخ پایان</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($plans as $plan): ?>
                                <tr>
                                    <td>
                                        <a href="/audit-plans/view/<?= $plan['id'] ?>" class="text-decoration-none">
                                            <strong><?= htmlspecialchars($plan['title']) ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $plan['audit_type'] === 'internal' ? 'info' : 'warning' ?>">
                                            <?= $plan['audit_type'] === 'internal' ? 'داخلی' : 'خارجی' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($plan['lead_auditor_name'] ?? '-') ?></td>
                                    <td><?= date('Y/m/d', strtotime($plan['start_date'])) ?></td>
                                    <td><?= date('Y/m/d', strtotime($plan['end_date'])) ?></td>
                                    <td>
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
                                        <span class="badge badge-<?= $statusColors[$plan['status']] ?? 'secondary' ?>">
                                            <?= $statusLabels[$plan['status']] ?? $plan['status'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="/audit-plans/view/<?= $plan['id'] ?>" class="btn btn-sm btn-info" title="مشاهده">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($_SESSION['user_role'] === 'admin' || $plan['user_id'] == $_SESSION['user_id']): ?>
                                                <a href="/audit-plans/edit/<?= $plan['id'] ?>" class="btn btn-sm btn-warning" title="ویرایش">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                                                <form method="POST" action="/audit-plans/delete/<?= $plan['id'] ?>" 
                                                      onsubmit="return confirm('آیا از حذف این برنامه مطمئن هستید؟');" 
                                                      style="display: inline;">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
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
</div>