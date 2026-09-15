<?php
// views/admin/vouchers.php
$stats = ['unused' => 0, 'used' => 0, 'expired' => 0];
foreach ($vouchers as $v) {
    $stats[$v['status']] = ($stats[$v['status']] ?? 0) + 1;
}
?>

<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-ticket-alt"></i> کدهای اشتراک</h1>
                <p class="subtitle">تولید و مدیریت کدهای اشتراک برای فروش از طریق تلگرام یا سازمان‌ها</p>
            </div>
        </div>

        <!-- فرم تولید -->
        <div class="admin-card">
            <h3>🎯 تولید کد جدید</h3>
            <form method="POST" class="voucher-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>طرح</label>
                        <select name="plan_id" required>
                            <?php foreach ($plans as $pl): ?>
                                <?php if ($pl['price_monthly'] > 0): ?>
                                    <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>دوره</label>
                        <select name="period">
                            <option value="monthly">ماهانه</option>
                            <option value="yearly">سالانه</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>تعداد</label>
                        <input type="number" name="count" value="1" min="1" max="50" required>
                    </div>

                    <div class="form-group">
                        <label>اعتبار (روز)</label>
                        <input type="number" name="expire_days" value="90" min="1" max="365" required>
                    </div>

                    <div class="form-group" style="flex:0 0 auto;">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn-admin-submit">
                            <i class="fas fa-magic"></i> تولید کد
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- آمار -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['unused'] ?></span>
                    <span class="stat-label">استفاده نشده</span>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i class="fas fa-check"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['used'] ?></span>
                    <span class="stat-label">استفاده شده</span>
                </div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-calendar-times"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $stats['expired'] ?></span>
                    <span class="stat-label">منقضی</span>
                </div>
            </div>
        </div>

        <!-- جدول -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>کد</th>
                        <th>طرح</th>
                        <th>دوره</th>
                        <th>وضعیت</th>
                        <th>استفاده‌کننده</th>
                        <th>انقضا</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($vouchers as $v):
                    $statusClass = [
                        'unused' => 'success',
                        'used' => 'secondary',
                        'expired' => 'danger',
                    ][$v['status']] ?? 'secondary';
                ?>
                    <tr>
                        <td>
                            <code class="voucher-code" onclick="copyVoucher(this, '<?= htmlspecialchars($v['code']) ?>')">
                                <?= htmlspecialchars($v['code']) ?>
                            </code>
                            <small class="copy-hint">کلیک برای کپی</small>
                        </td>
                        <td><?= htmlspecialchars($v['plan_name']) ?></td>
                        <td><?= $v['period'] === 'yearly' ? 'سالانه' : 'ماهانه' ?></td>
                        <td><span class="status-badge <?= $statusClass ?>"><?= $v['status'] ?></span></td>
                        <td><?= htmlspecialchars($v['used_by_name'] ?? '-') ?></td>
                        <td>
                            <?php if ($v['expires_at']): ?>
                                <small><?= jdate($v['expires_at'], 'j F Y') ?></small>
                            <?php else: ?>-<?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($vouchers)): ?>
                    <tr><td colspan="6" style="text-align:center; padding:40px;">کدی تولید نشده است.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function copyVoucher(el, code) {
    navigator.clipboard.writeText(code);
    const hint = el.parentElement.querySelector('.copy-hint');
    if (hint) {
        hint.textContent = '✓ کپی شد!';
        setTimeout(() => hint.textContent = 'کلیک برای کپی', 2000);
    }
}
</script>