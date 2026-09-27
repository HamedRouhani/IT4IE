<?php
$statusLabels = [
    'open'        => ['label' => 'باز', 'class' => 'warning'],
    'in_progress' => ['label' => 'در حال اجرا', 'class' => 'info'],
    'implemented' => ['label' => 'اجرا شد', 'class' => 'info'],
    'verified'    => ['label' => 'تأییدشده', 'class' => 'success'],
    'closed'      => ['label' => 'بسته‌شده', 'class' => 'inactive'],
    'cancelled'   => ['label' => 'لغو‌شده', 'class' => 'inactive'],
];
$priorityLabels = [
    'critical' => ['label' => 'بحرانی', 'color' => '#dc2626'],
    'high'     => ['label' => 'بالا',   'color' => '#f59e0b'],
    'medium'   => ['label' => 'متوسط',  'color' => '#3b82f6'],
    'low'      => ['label' => 'پایین',  'color' => '#6b7280'],
];
$effectivenessLabels = [
    'pending'             => ['label' => 'در انتظار', 'class' => 'info'],
    'effective'           => ['label' => 'مؤثر', 'class' => 'success'],
    'partially_effective' => ['label' => 'نسبتاً مؤثر', 'class' => 'warning'],
    'not_effective'       => ['label' => 'غیرمؤثر', 'class' => 'danger'],
];
$statusMeta = $statusLabels[$capa['status']] ?? ['label' => $capa['status'], 'class' => 'info'];
$prioMeta   = $priorityLabels[$capa['priority']] ?? ['label' => $capa['priority'], 'color' => '#6b7280'];
$effMeta    = $effectivenessLabels[$capa['effectiveness']] ?? ['label' => $capa['effectiveness'], 'class' => 'info'];

$today = date('Y-m-d');
$isOverdue = $capa['due_date'] && $capa['due_date'] < $today
          && !in_array($capa['status'], ['closed', 'cancelled', 'verified']);

// Stepper
$steps = [
    'open'        => ['label' => 'باز', 'icon' => 'fa-folder-open'],
    'in_progress' => ['label' => 'در حال اجرا', 'icon' => 'fa-spinner'],
    'implemented' => ['label' => 'اجرا شد', 'icon' => 'fa-check'],
    'verified'    => ['label' => 'تأییدشده', 'icon' => 'fa-shield-alt'],
    'closed'      => ['label' => 'بسته‌شده', 'icon' => 'fa-lock'],
];
$currentIdx = array_search($capa['status'], array_keys($steps));
if ($currentIdx === false) $currentIdx = -1;
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <div style="font-size:0.85rem; color:#94a3b8; margin-bottom:4px;">
                <a href="<?= CURRENT_MODULE_URL ?>?controller=capa" style="color:#059669; text-decoration:none;">
                    <i class="fas fa-arrow-right"></i> بازگشت به لیست
                </a>
            </div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <?= htmlspecialchars($capa['title']) ?>
            </h1>
            <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                <span style="background:<?= $prioMeta['color'] ?>; color:#fff; padding:3px 12px; border-radius:4px; font-size:0.8rem; font-weight:600;">
                    <i class="fas fa-flag"></i> <?= $prioMeta['label'] ?>
                </span>
                <span class="qc-status-badge qc-status-<?= $statusMeta['class'] ?>">
                    <?= $statusMeta['label'] ?>
                </span>
                <?php if ($isOverdue): ?>
                    <span class="qc-status-badge qc-status-danger">
                        <i class="fas fa-exclamation-triangle"></i> سررسید گذشته
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=edit&id=<?= $capa['id'] ?>" class="qc-btn-outline">
            <i class="fas fa-edit"></i> ویرایش
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="qc-alert qc-alert-success qc-mb-3">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Stepper گردش کار -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-body">
            <div style="display:flex; align-items:flex-start; justify-content:space-between;">
                <?php $i = 0; foreach ($steps as $key => $step):
                    $isDone    = $i < $currentIdx;
                    $isCurrent = $i === $currentIdx;
                    $circleColor = $isDone ? '#059669' : ($isCurrent ? '#3b82f6' : '#e5e7eb');
                    $circleText  = ($isDone || $isCurrent) ? '#fff' : '#94a3b8';
                    $labelColor  = $isCurrent ? '#1f2937' : ($isDone ? '#059669' : '#94a3b8');
                ?>
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; position:relative;">
                        
                        <!-- خط سمت چپ دایره (به جز اولین) -->
                        <?php if ($i > 0): ?>
                            <div style="position:absolute; top:20px; right:50%; width:100%; height:2px;
                                        background:<?= $i <= $currentIdx ? '#059669' : '#e5e7eb' ?>;
                                        transform:translateY(-50%); z-index:0;"></div>
                        <?php endif; ?>
                        
                        <!-- دایره -->
                        <div style="width:40px; height:40px; border-radius:50%;
                                    background:<?= $circleColor ?>;
                                    color:<?= $circleText ?>;
                                    display:flex; align-items:center; justify-content:center;
                                    font-size:1rem; font-weight:700;
                                    position:relative; z-index:1;
                                    border:3px solid #fff;
                                    box-shadow:0 0 0 1px <?= $circleColor ?>;">
                            <i class="fas <?= $isDone ? 'fa-check' : $step['icon'] ?>"></i>
                        </div>
                        
                        <!-- برچسب -->
                        <div style="font-size:0.8rem; font-weight:<?= $isCurrent ? '700' : '500' ?>;
                                    color:<?= $labelColor ?>;
                                    margin-top:8px; text-align:center;">
                            <?= $step['label'] ?>
                        </div>
                    </div>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Quick Status Update -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-body" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <span style="font-size:0.85rem; color:#64748b; font-weight:600;">تغییر سریع وضعیت:</span>
            <?php foreach ($statusLabels as $k => $m): ?>
                <button type="button"
                        class="qc-btn-outline qc-quick-status-btn <?= $capa['status'] === $k ? 'active' : '' ?>"
                        data-id="<?= $capa['id'] ?>"
                        data-status="<?= $k ?>"
                        style="<?= $capa['status'] === $k ? 'background:' . ($m['class'] === 'success' ? '#d1fae5' : '#e0f2fe') . '; border-color:transparent;' : '' ?>">
                    <?= $m['label'] ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($sourceInfo): ?>
        <div class="qc-card qc-mb-3" style="border-right:4px solid #dc2626;">
            <div class="qc-card-body">
                <div style="font-size:0.8rem; color:#94a3b8; margin-bottom:6px;">
                    <i class="fas fa-link"></i> منبع این اقدام
                </div>
                <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $sourceInfo['id'] ?>"
                   style="color:#059669; font-weight:600; text-decoration:none; font-size:1rem;">
                    <i class="fas fa-chart-bar"></i> تحلیل Pareto: <?= htmlspecialchars($sourceInfo['title']) ?>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:1rem;">

        <div>
            <?php if ($capa['problem_description']): ?>
                <div class="qc-card qc-mb-3">
                    <div class="qc-card-header">
                        <h3 class="qc-card-title"><i class="fas fa-search"></i> شرح مشکل</h3>
                    </div>
                    <div class="qc-card-body">
                        <p style="line-height:1.7; color:#334155; margin:0;">
                            <?= nl2br(htmlspecialchars($capa['problem_description'])) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($capa['root_cause']): ?>
                <div class="qc-card qc-mb-3">
                    <div class="qc-card-header">
                        <h3 class="qc-card-title">
                            <i class="fas fa-sitemap"></i> ریشه‌ی مشکل
                            <?php if ($capa['root_cause_method']): ?>
                                <span class="qc-chart-badge"><?= htmlspecialchars($capa['root_cause_method']) ?></span>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="qc-card-body">
                        <p style="line-height:1.7; color:#334155; margin:0;">
                            <?= nl2br(htmlspecialchars($capa['root_cause'])) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($capa['action_plan']): ?>
                <div class="qc-card qc-mb-3">
                    <div class="qc-card-header">
                        <h3 class="qc-card-title"><i class="fas fa-clipboard-list"></i> برنامه‌ی اقدام</h3>
                    </div>
                    <div class="qc-card-body">
                        <p style="line-height:1.7; color:#334155; margin:0;">
                            <?= nl2br(htmlspecialchars($capa['action_plan'])) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($capa['before_value'] !== null || $capa['after_value'] !== null): ?>
                <div class="qc-card qc-mb-3">
                    <div class="qc-card-header qc-flex qc-flex-between">
                        <h3 class="qc-card-title"><i class="fas fa-chart-line"></i> اثربخشی اقدام</h3>
                        <span class="qc-status-badge qc-status-<?= $effMeta['class'] ?>">
                            <?= $effMeta['label'] ?>
                        </span>
                    </div>
                    <div class="qc-card-body">
                        <?php
                        $before = (float)($capa['before_value'] ?? 0);
                        $after  = (float)($capa['after_value'] ?? 0);
                        $improvement = null;
                        if ($before > 0 && $capa['after_value'] !== null) {
                            $improvement = (($before - $after) / $before) * 100;
                        }
                        ?>
                        <div class="qc-stats-grid">
                            <div class="qc-stat-card">
                                <div class="qc-stat-icon" style="background:#fee2e2; color:#dc2626;">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                                <div>
                                    <div class="qc-stat-value"><?= number_format($before, 2) ?></div>
                                    <div class="qc-stat-label">قبل از اقدام</div>
                                </div>
                            </div>
                            <div class="qc-stat-card success">
                                <div class="qc-stat-icon" style="background:#d1fae5; color:#047857;">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div>
                                    <div class="qc-stat-value"><?= number_format($after, 2) ?></div>
                                    <div class="qc-stat-label">بعد از اقدام</div>
                                </div>
                            </div>
                            <?php if ($improvement !== null): ?>
                                <div class="qc-stat-card <?= $improvement >= 20 ? 'success' : 'danger' ?>">
                                    <div class="qc-stat-icon" style="background:<?= $improvement >= 20 ? '#d1fae5' : '#fee2e2' ?>; color:<?= $improvement >= 20 ? '#047857' : '#dc2626' ?>;">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div>
                                        <div class="qc-stat-value"><?= number_format($improvement, 1) ?>٪</div>
                                        <div class="qc-stat-label">بهبود</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($capa['effectiveness_notes']): ?>
                            <div style="margin-top:1rem; padding:12px; background:#f0fdf4; border-radius:6px; border-right:3px solid #059669;">
                                <div style="font-size:0.75rem; color:#047857; font-weight:700; margin-bottom:4px;">
                                    یادداشت اثربخشی:
                                </div>
                                <p style="color:#334155; margin:0; font-size:0.9rem;">
                                    <?= nl2br(htmlspecialchars($capa['effectiveness_notes'])) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <div class="qc-card qc-mb-3">
                <div class="qc-card-header">
                    <h3 class="qc-card-title"><i class="fas fa-info-circle"></i> اطلاعات پایه</h3>
                </div>
                <div class="qc-card-body">
                    <div style="display:flex; flex-direction:column; gap:14px; font-size:0.9rem;">
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">نوع اقدام</div>
                            <div style="color:#1f2937; font-weight:600;">
                                <?php
                                $typeLabels = ['corrective' => 'اصلاحی', 'preventive' => 'پیشگیرانه', 'improvement' => 'بهبود'];
                                echo $typeLabels[$capa['action_type']] ?? $capa['action_type'];
                                ?>
                            </div>
                        </div>
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">مسئول</div>
                            <div style="color:#1f2937; font-weight:600;">
                                <?= htmlspecialchars($capa['responsible_person'] ?? '—') ?>
                            </div>
                        </div>
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">دپارتمان</div>
                            <div style="color:#1f2937; font-weight:600;">
                                <?= htmlspecialchars($capa['department'] ?? '—') ?>
                            </div>
                        </div>
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">سررسید</div>
                            <div style="color:<?= $isOverdue ? '#dc2626' : '#1f2937' ?>; font-weight:600;">
                                <?= !empty($capa['due_date']) ? qc_date($capa['due_date'], 'Y/m/d') : '—' ?>
                            </div>
                        </div>
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">تاریخ اجرا</div>
                            <div style="color:#1f2937; font-weight:600;">
                                <?= !empty($capa['implemented_date']) ? qc_date($capa['implemented_date'], 'Y/m/d') : '—' ?>
                            </div>
                        </div>
                        <div>
                            <div style="color:#94a3b8; font-size:0.75rem; margin-bottom:2px;">تاریخ تأیید</div>
                            <div style="color:#1f2937; font-weight:600;">
                                <?= !empty($capa['verification_date']) ? qc_date($capa['verification_date'], 'Y/m/d') : '—' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($capa['estimated_cost'] !== null || $capa['actual_cost'] !== null): ?>
                <div class="qc-card qc-mb-3">
                    <div class="qc-card-header">
                        <h3 class="qc-card-title"><i class="fas fa-coins"></i> هزینه</h3>
                    </div>
                    <div class="qc-card-body">
                        <div style="display:flex; flex-direction:column; gap:12px; font-size:0.9rem;">
                            <div>
                                <div style="color:#94a3b8; font-size:0.75rem;">برآوردی</div>
                                <div style="color:#1f2937; font-weight:700;">
                                    <?= number_format((float)($capa['estimated_cost'] ?? 0), 0) ?>
                                </div>
                            </div>
                            <div>
                                <div style="color:#94a3b8; font-size:0.75rem;">واقعی</div>
                                <div style="color:#1f2937; font-weight:700;">
                                    <?= number_format((float)($capa['actual_cost'] ?? 0), 0) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="qc-flex qc-gap-2" style="justify-content:flex-end; margin-top:1rem;">
        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa" class="qc-btn-outline">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
        <button type="button"
                class="qc-btn-danger qc-confirm-delete"
                data-url="<?= CURRENT_MODULE_URL ?>?controller=capa&action=delete"
                data-id="<?= $capa['id'] ?>"
                data-message="این CAPA حذف خواهد شد. مطمئنی؟">
            <i class="fas fa-trash"></i> حذف
        </button>
    </div>
</div>

<script src="/public/assets/js/software/quality.js"></script>
<script>
window.QC_QUICK_STATUS_URL = '<?= CURRENT_MODULE_URL ?>?controller=capa&action=quickStatus';
window.QC_CSRF_TOKEN = '<?= $this->csrfToken() ?>';
</script>