<?php
// views/admin/leads.php
$scoreStats = ['hot' => 0, 'warm' => 0, 'cold' => 0];
foreach ($leads as $l) {
    if ($l['lead_score'] >= 75) $scoreStats['hot']++;
    elseif ($l['lead_score'] >= 50) $scoreStats['warm']++;
    else $scoreStats['cold']++;
}
?>

<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>

    <div class="admin-content">
        <div class="admin-header">
            <div>
                <h1><i class="fas fa-bullseye"></i> مدیریت لیدها</h1>
                <p class="subtitle">کاربران داغ بر اساس ارزیابی چک‌لیست و اطلاعات تماس</p>
            </div>
        </div>

        <!-- آمار لیدها -->
        <div class="stats-grid">
            <div class="stat-card hot">
                <div class="stat-icon"><i class="fas fa-fire"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $scoreStats['hot'] ?></span>
                    <span class="stat-label">لید داغ (۷۵+)</span>
                </div>
            </div>
            <div class="stat-card warm">
                <div class="stat-icon"><i class="fas fa-temperature-high"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $scoreStats['warm'] ?></span>
                    <span class="stat-label">لید گرم (۵۰+)</span>
                </div>
            </div>
            <div class="stat-card cold">
                <div class="stat-icon"><i class="fas fa-snowflake"></i></div>
                <div class="stat-info">
                    <span class="stat-value"><?= $scoreStats['cold'] ?></span>
                    <span class="stat-label">لید سرد</span>
                </div>
            </div>
        </div>

        <!-- فیلترها -->
        <div class="filter-bar">
            <form method="GET" action="/admin/leads" class="filter-form">
                <select name="min" onchange="this.form.submit()">
                    <option value="0" <?= $minScore === 0 ? 'selected' : '' ?>>همه امتیازها</option>
                    <option value="50" <?= $minScore === 50 ? 'selected' : '' ?>>امتیاز ≥ ۵۰ (گرم و داغ)</option>
                    <option value="75" <?= $minScore === 75 ? 'selected' : '' ?>>فقط داغ‌ها (≥ ۷۵)</option>
                </select>
                <select name="status" onchange="this.form.submit()">
                    <option value="" <?= $statusFilter === '' ? 'selected' : '' ?>>همه وضعیت‌ها</option>
                    <option value="new" <?= $statusFilter === 'new' ? 'selected' : '' ?>>جدید</option>
                    <option value="contacted" <?= $statusFilter === 'contacted' ? 'selected' : '' ?>>تماس گرفته‌شده</option>
                    <option value="qualified" <?= $statusFilter === 'qualified' ? 'selected' : '' ?>>صلاحیت‌دار</option>
                    <option value="closed" <?= $statusFilter === 'closed' ? 'selected' : '' ?>>بسته</option>
                </select>
            </form>
        </div>

        <!-- جدول لیدها -->
        <div class="admin-table">
            <table>
                <thead>
                    <tr>
                        <th>امتیاز</th>
                        <th>مشخصات</th>
                        <th>چک‌لیست</th>
                        <th>ریسک</th>
                        <th>تاریخ</th>
                        <th>وضعیت لید</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($leads as $lead):
                    $score = (int)$lead['lead_score'];
                    if ($score >= 75) {
                        $scoreColor = '#dc3545';
                        $scoreLabel = 'داغ 🔥';
                    } elseif ($score >= 50) {
                        $scoreColor = '#fd7e14';
                        $scoreLabel = 'گرم';
                    } else {
                        $scoreColor = '#28a745';
                        $scoreLabel = 'سرد';
                    }
                    $riskColors = [
                        'critical' => '#dc3545',
                        'high' => '#fd7e14',
                        'medium' => '#ffc107',
                        'low' => '#28a745',
                    ];
                ?>
                    <tr>
                        <td class="score-cell">
                            <div class="score-circle" style="--score-color: <?= $scoreColor ?>">
                                <span class="score-value"><?= $score ?></span>
                            </div>
                            <small><?= $scoreLabel ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($lead['name']) ?></strong>
                            <br>
                            <small style="color:var(--gray)">
                                <i class="fas fa-building"></i> <?= htmlspecialchars($lead['company'] ?: '-') ?>
                            </small>
                            <?php if (!empty($lead['email'])): ?>
                                <br><small style="color:var(--gray)"><?= htmlspecialchars($lead['email']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($lead['checklist_title'] ?? '-') ?></td>
                        <td>
                            <span class="risk-badge" style="background: <?= $riskColors[$lead['risk_level']] ?? '#ccc' ?>">
                                <?= htmlspecialchars($lead['risk_level']) ?>
                            </span>
                        </td>
                        <td><small><?= jdate($lead['created_at'], 'j F') ?></small></td>
                        <td>
                            <form method="POST" action="/admin/leads/status/<?= $lead['id'] ?>" class="status-form">
                                <select name="lead_status" onchange="this.form.submit()">
                                    <option value="new" <?= $lead['lead_status'] === 'new' ? 'selected' : '' ?>>جدید</option>
                                    <option value="contacted" <?= $lead['lead_status'] === 'contacted' ? 'selected' : '' ?>>تماس گرفته‌شده</option>
                                    <option value="qualified" <?= $lead['lead_status'] === 'qualified' ? 'selected' : '' ?>>صلاحیت‌دار</option>
                                    <option value="closed" <?= $lead['lead_status'] === 'closed' ? 'selected' : '' ?>>بسته</option>
                                </select>
                            </form>
                        </td>
                        <td class="actions">
                            <a href="/admin/checklist/result/<?= $lead['id'] ?>" class="btn-action view" title="مشاهده نتیجه">
                                <i class="fas fa-poll"></i>
                            </a>
                            <a href="/admin/checklist/message/<?= $lead['id'] ?>" class="btn-action email" title="ارسال پیام">
                                <i class="fas fa-paper-plane"></i>
                            </a>
                            <?php if (!empty($lead['phone'])): ?>
                                <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn-action call" title="تماس">
                                    <i class="fas fa-phone"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding:40px; color:var(--gray)">
                            <i class="fas fa-user-slash" style="font-size:2rem; opacity:.3"></i>
                            <br>لیدی یافت نشد.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>