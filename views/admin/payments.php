<?php
// views/admin/payments.php
$statusLabels = [
    'awaiting_ref' => 'در انتظار کد پیگیری',
    'pending_review' => 'در انتظار بررسی',
    'awaiting_contact' => 'درخواست پیش‌فاکتور',
    'approved' => 'تأیید شده',
    'rejected' => 'رد شده',
];
$statusCounts = [];
foreach ($payments as $p) {
    $statusCounts[$p['status']] = ($statusCounts[$p['status']] ?? 0) + 1;
}
?>

<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-credit-card"></i> بررسی پرداخت‌ها</h1>
                <p class="subtitle">مدیریت واریزهای کارت‌به‌کارت، کدهای اشتراک و درخواست‌های پیش‌فاکتور</p>
            </div>
        </div>

        <!-- کارت‌های آماری -->
        <div class="stats-grid">
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $statusCounts['pending_review'] ?? 0 ?></span>
                    <span class="stat-label">در انتظار بررسی</span>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $statusCounts['awaiting_contact'] ?? 0 ?></span>
                    <span class="stat-label">درخواست پیش‌فاکتور</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i class="fas fa-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $statusCounts['approved'] ?? 0 ?></span>
                    <span class="stat-label">تأیید شده</span>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-times"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $statusCounts['rejected'] ?? 0 ?></span>
                    <span class="stat-label">رد شده</span>
                </div>
            </div>
        </div>

        <!-- فیلتر -->
        <div class="filter-bar">
            <form method="GET" action="/admin/payments" class="filter-form">
                <select name="status" onchange="this.form.submit()">
                    <option value="">همه وضعیت‌ها (<?= count($payments) ?>)</option>
                    <?php foreach ($statusLabels as $k => $label): ?>
                        <option value="<?= $k ?>" <?= $statusFilter === $k ? 'selected' : '' ?>>
                            <?= $label ?> (<?= $statusCounts[$k] ?? 0 ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- جدول -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>کاربر</th>
                        <th>طرح</th>
                        <th>مبلغ</th>
                        <th>مبلغ انتظار</th>
                        <th>کد پیگیری</th>
                        <th>روش</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($payments as $p):
                    $statusClass = [
                        'pending_review' => 'warning',
                        'awaiting_ref' => 'secondary',
                        'awaiting_contact' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    ][$p['status']] ?? 'secondary';
                ?>
                    <tr>
                        <td>#<?= $p['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['user_name'] ?? '-') ?></strong>
                            <br><small style="color:var(--gray)"><?= htmlspecialchars($p['user_email'] ?? '') ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['plan_name'] ?? '-') ?></td>
                        <td><?= number_format($p['amount']) ?></td>
                        <td><strong style="color:var(--primary)"><?= number_format($p['expected_amount']) ?></strong></td>
                        <td dir="ltr" class="ref-cell">
                            <?= !empty($p['ref_code']) ? '<code>' . htmlspecialchars($p['ref_code']) . '</code>' : '-' ?>
                        </td>
                        <td>
                            <?php if ($p['method'] === 'invoice'): ?>
                                <span class="method-tag invoice">پیش‌فاکتور</span>
                            <?php elseif ($p['method'] === 'voucher'): ?>
                                <span class="method-tag voucher">کد</span>
                            <?php else: ?>
                                <span class="method-tag card">کارت</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= htmlspecialchars($statusLabels[$p['status']] ?? $p['status']) ?>
                            </span>
                        </td>
                        <td class="actions">
                            <?php if ($p['status'] === 'pending_review'): ?>
                            <form method="POST"
                                action="/admin/payments/review/<?= (int)$p['id'] ?>"
                                class="review-form">

                                <input type="text"
                                    name="admin_note"
                                    placeholder="یادداشت..."
                                    style="width:110px; padding:6px 8px; font-size:.75rem;">

                                <button type="submit"
                                        name="action"
                                        value="approve"
                                        class="btn-action approve"
                                        title="تأیید و فعال‌سازی"
                                        style="display:inline-flex !important; width:80px; height:34px; background:#28a745 !important; color:#fff !important; border:0; border-radius:6px; align-items:center; justify-content:center; cursor:pointer;">
                                    تأیید
                                </button>

                                <button type="submit"
                                        name="action"
                                        value="reject"
                                        class="btn-action reject"
                                        title="رد پرداخت"
                                        onclick="return confirm('پرداخت رد شود؟');"
                                        style="display:inline-flex !important; width:55px; height:34px; background:#dc3545 !important; color:#fff !important; border:0; border-radius:6px; align-items:center; justify-content:center; cursor:pointer;">
                                    رد
                                </button>

                            </form>
                            <?php elseif ($p['status'] === 'awaiting_contact'): ?>

                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">

                                    <a href="mailto:<?= htmlspecialchars($p['user_email'] ?? '') ?>?subject=<?= urlencode('پیش‌فاکتور IT4IE - ' . ($p['plan_name'] ?? '')) ?>"
                                    class="btn-action email"
                                    title="ارسال پیش‌فاکتور">
                                        <i class="fas fa-envelope"></i>
                                        ارسال
                                    </a>

                                    <form method="POST"
                                        action="/admin/payments/review/<?= (int)$p['id'] ?>"
                                        class="review-form"
                                        style="display:flex; gap:5px; align-items:center;">

                                        <input type="text"
                                            name="admin_note"
                                            placeholder="یادداشت..."
                                            style="width:110px; padding:6px 8px; font-size:.75rem;">

                                        <button type="submit"
                                                name="action"
                                                value="approve"
                                                class="btn-action approve"
                                                title="تأیید پرداخت و فعال‌سازی"
                                                style="display:inline-flex !important; width:80px; height:34px; background:#28a745 !important; color:#fff !important; border:0; border-radius:6px; align-items:center; justify-content:center; cursor:pointer;">
                                            تأیید
                                        </button>

                                        <button type="submit"
                                                name="action"
                                                value="reject"
                                                class="btn-action reject"
                                                title="رد درخواست"
                                                onclick="return confirm('این درخواست پیش‌فاکتور رد شود؟');"
                                                style="display:inline-flex !important; width:55px; height:34px; background:#dc3545 !important; color:#fff !important; border:0; border-radius:6px; align-items:center; justify-content:center; cursor:pointer;">
                                            رد
                                        </button>

                                    </form>

                                    <small style="display:block; color:var(--gray); font-size:.72rem; max-width:260px;">
                                        <?= htmlspecialchars($p['note'] ?? '') ?>
                                    </small>

                                </div>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding:40px; color:var(--gray)">
                            <i class="fas fa-inbox" style="font-size:2rem; opacity:.3"></i>
                            <br>پرداختی یافت نشد.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>