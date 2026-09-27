<?php
$statusLabels = [
    'open'         => ['label' => 'باز', 'class' => 'warning'],
    'in_progress'  => ['label' => 'در حال اجرا', 'class' => 'info'],
    'implemented'  => ['label' => 'اجرا شد', 'class' => 'info'],
    'verified'     => ['label' => 'تأییدشده', 'class' => 'success'],
    'closed'       => ['label' => 'بسته‌شده', 'class' => 'inactive'],
    'cancelled'    => ['label' => 'لغو‌شده', 'class' => 'inactive'],
];
$priorityLabels = [
    'critical' => ['label' => 'بحرانی', 'color' => '#dc2626'],
    'high'     => ['label' => 'بالا',   'color' => '#f59e0b'],
    'medium'   => ['label' => 'متوسط',  'color' => '#3b82f6'],
    'low'      => ['label' => 'پایین',  'color' => '#6b7280'],
];
$sourceLabels = [
    'pareto'              => 'Pareto',
    'control_chart'       => 'نمودار کنترل',
    'capability'          => 'قابلیت فرآیند',
    'msa'                 => 'MSA',
    'fmea'                => 'FMEA',
    'audit'               => 'ممیزی',
    'customer_complaint'  => 'شکایت مشتری',
    'other'               => 'سایر',
];
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <i class="fas fa-tasks" style="color:#059669;"></i>
                اقدامات اصلاحی (CAPA)
            </h1>
            <p style="color:#6b7280; margin-top:4px; font-size:0.875rem;">
                پیگیری و مدیریت اقدامات اصلاحی و پیشگیرانه
            </p>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=create" class="qc-btn-primary">
            <i class="fas fa-plus"></i> CAPA جدید
        </a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="qc-alert qc-alert-success qc-mb-3">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="qc-stats-grid qc-mb-4">
        <div class="qc-stat-card">
            <div class="qc-stat-icon" style="background:#e0f2fe; color:#0369a1;">
                <i class="fas fa-tasks"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)$stats['total'] ?></div>
                <div class="qc-stat-label">کل اقدامات</div>
            </div>
        </div>
        <div class="qc-stat-card warning">
            <div class="qc-stat-icon" style="background:#fef3c7; color:#b45309;">
                <i class="fas fa-spinner"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)($stats['open'] + $stats['in_progress']) ?></div>
                <div class="qc-stat-label">در جریان</div>
            </div>
        </div>
        <div class="qc-stat-card danger">
            <div class="qc-stat-icon" style="background:#fee2e2; color:#dc2626;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)$stats['overdue'] ?></div>
                <div class="qc-stat-label">سررسید گذشته</div>
            </div>
        </div>
        <div class="qc-stat-card success">
            <div class="qc-stat-icon" style="background:#d1fae5; color:#047857;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)($stats['closed'] + $stats['verified']) ?></div>
                <div class="qc-stat-label">تکمیل‌شده</div>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-body" style="padding:0;">
            <div style="display:flex; border-bottom:1px solid #e5e7eb;">
                <?php
                $views = [
                    'all'      => ['label' => 'همه', 'icon' => 'fa-list', 'count' => (int)$stats['total']],
                    'active'   => ['label' => 'در جریان', 'icon' => 'fa-spinner', 'count' => (int)($stats['open'] + $stats['in_progress'] + $stats['implemented'])],
                    'overdue'  => ['label' => 'سررسید گذشته', 'icon' => 'fa-exclamation-triangle', 'count' => (int)$stats['overdue']],
                    'critical' => ['label' => 'بحرانی', 'icon' => 'fa-fire', 'count' => (int)$stats['critical']],
                ];
                foreach ($views as $key => $v):
                    $isActive = $filters['view'] === $key;
                    $url = CURRENT_MODULE_URL . '?controller=capa&view=' . $key;
                ?>
                    <a href="<?= $url ?>" style="flex:1; padding:12px 16px; text-decoration:none; color:<?= $isActive ? '#059669' : '#64748b' ?>;
                                                border-bottom:3px solid <?= $isActive ? '#059669' : 'transparent' ?>;
                                                font-weight:<?= $isActive ? '700' : '500' ?>; font-size:0.9rem;
                                                display:flex; align-items:center; justify-content:center; gap:8px;">
                        <i class="fas <?= $v['icon'] ?>"></i>
                        <?= $v['label'] ?>
                        <span style="background:<?= $isActive ? '#d1fae5' : '#f1f5f9' ?>;
                                     color:<?= $isActive ? '#047857' : '#64748b' ?>;
                                     padding:2px 8px; border-radius:10px; font-size:0.75rem;">
                            <?= $v['count'] ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="qc-card qc-mb-3">
        <div class="qc-card-body">
            <form method="GET" class="qc-flex qc-gap-2" style="flex-wrap:wrap; align-items:flex-end;">
                <input type="hidden" name="controller" value="capa">
                <input type="hidden" name="view" value="<?= htmlspecialchars($filters['view']) ?>">
                <div style="flex:1; min-width:200px;">
                    <label class="qc-form-label">جستجو</label>
                    <input type="text" name="search" class="qc-form-control"
                           value="<?= htmlspecialchars($filters['search']) ?>"
                           placeholder="عنوان یا شرح مشکل...">
                </div>
                <div style="min-width:140px;">
                    <label class="qc-form-label">وضعیت</label>
                    <select name="status" class="qc-form-select">
                        <option value="">همه</option>
                        <?php foreach ($statusLabels as $k => $m): ?>
                            <option value="<?= $k ?>" <?= $filters['status'] === $k ? 'selected' : '' ?>>
                                <?= $m['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="qc-form-label">اولویت</label>
                    <select name="priority" class="qc-form-select">
                        <option value="">همه</option>
                        <?php foreach ($priorityLabels as $k => $m): ?>
                            <option value="<?= $k ?>" <?= $filters['priority'] === $k ? 'selected' : '' ?>>
                                <?= $m['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:140px;">
                    <label class="qc-form-label">منبع</label>
                    <select name="source_type" class="qc-form-select">
                        <option value="">همه</option>
                        <?php foreach ($sourceLabels as $k => $l): ?>
                            <option value="<?= $k ?>" <?= $filters['source_type'] === $k ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="qc-btn-primary">
                    <i class="fas fa-filter"></i> اعمال
                </button>
                <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&view=<?= htmlspecialchars($filters['view']) ?>" class="qc-btn-outline">
                    <i class="fas fa-times"></i> پاک‌سازی
                </a>
            </form>
        </div>
    </div>

    <?php if (empty($capas)): ?>
        <div class="qc-empty-state">
            <i class="fas fa-tasks" style="font-size:3rem; color:#cbd5e1;"></i>
            <h3 style="margin-top:1rem; color:#64748b;">هیچ CAPA ای پیدا نشد</h3>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=create" class="qc-btn-primary" style="margin-top:1rem;">
                <i class="fas fa-plus"></i> ایجاد CAPA
            </a>
        </div>
    <?php else: ?>
        <div class="qc-card">
            <div class="qc-table-wrapper" style="overflow-x:auto;">
                <table class="qc-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">#</th>
                            <th>عنوان</th>
                            <th>منبع</th>
                            <th>اولویت</th>
                            <th>مسئول</th>
                            <th>سررسید</th>
                            <th>وضعیت</th>
                            <th style="width:140px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $today = date('Y-m-d');
                        foreach ($capas as $i => $c):
                            $statusMeta = $statusLabels[$c['status']] ?? ['label' => $c['status'], 'class' => 'info'];
                            $prioMeta   = $priorityLabels[$c['priority']] ?? ['label' => $c['priority'], 'color' => '#6b7280'];
                            $isOverdue  = $c['due_date'] && $c['due_date'] < $today
                                       && !in_array($c['status'], ['closed', 'cancelled', 'verified']);
                        ?>
                            <tr style="<?= $isOverdue ? 'background:#fef2f2;' : '' ?>">
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=show&id=<?= $c['id'] ?>"
                                       style="color:#059669; font-weight:600; text-decoration:none;">
                                        <?= htmlspecialchars($c['title']) ?>
                                    </a>
                                    <?php if ($c['department']): ?>
                                        <div style="font-size:0.75rem; color:#94a3b8;">
                                            <i class="fas fa-building"></i> <?= htmlspecialchars($c['department']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="qc-chart-badge">
                                        <?= $sourceLabels[$c['source_type']] ?? $c['source_type'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background:<?= $prioMeta['color'] ?>; color:#fff; padding:3px 10px; border-radius:4px; font-size:0.75rem;">
                                        <?= $prioMeta['label'] ?>
                                    </span>
                                </td>
                                <td style="font-size:0.85rem;"><?= htmlspecialchars($c['responsible_person'] ?? '—') ?></td>
                                <td style="font-size:0.85rem;">
                                    <?php if ($c['due_date']): ?>
                                        <?= htmlspecialchars(qc_date($c['due_date'], 'Y/m/d')) ?>
                                        <?php if ($isOverdue): ?>
                                            <div style="font-size:0.7rem; color:#dc2626; font-weight:600;">
                                                <i class="fas fa-exclamation-triangle"></i> سررسید گذشته
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select class="qc-form-select qc-quick-status"
                                            style="padding:4px 8px; font-size:0.8rem; width:auto;"
                                            data-id="<?= $c['id'] ?>">
                                        <?php foreach ($statusLabels as $k => $m): ?>
                                            <option value="<?= $k ?>" <?= $c['status'] === $k ? 'selected' : '' ?>>
                                                <?= $m['label'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px;">
                                        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=show&id=<?= $c['id'] ?>"
                                           class="qc-btn-outline" style="padding:4px 10px; font-size:0.8rem;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= CURRENT_MODULE_URL ?>?controller=capa&action=edit&id=<?= $c['id'] ?>"
                                           class="qc-btn-outline" style="padding:4px 10px; font-size:0.8rem;">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="qc-btn-danger qc-confirm-delete"
                                                style="padding:4px 10px; font-size:0.8rem;"
                                                data-url="<?= CURRENT_MODULE_URL ?>?controller=capa&action=delete"
                                                data-id="<?= $c['id'] ?>"
                                                data-message="این CAPA حذف خواهد شد. مطمئنی؟">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="/public/assets/js/software/quality.js"></script>
<script>
window.QC_QUICK_STATUS_URL = '<?= CURRENT_MODULE_URL ?>?controller=capa&action=quickStatus';
window.QC_CSRF_TOKEN = '<?= $this->csrfToken() ?>';
</script>