<?php
$pageTitle = 'تحلیل‌های Pareto';
$statusLabels = [
    'draft'        => ['label' => 'پیش‌نویس', 'class' => 'info'],
    'analyzed'     => ['label' => 'تحلیل‌شده', 'class' => 'warning'],
    'action_taken' => ['label' => 'اقدام انجام شد', 'class' => 'success'],
    'closed'       => ['label' => 'بسته‌شده', 'class' => 'inactive'],
];
?>

<link rel="stylesheet" href="/public/assets/css/modules/quality.css?v=<?= time() ?>">

<div class="software-content qc-fade-in">

    <!-- Header -->
    <div class="qc-flex qc-flex-between qc-mb-4">
        <div>
            <h1 style="font-size:1.5rem; font-weight:700; color:#1f2937; margin:0;">
                <i class="fas fa-chart-bar" style="color:#059669;"></i>
                تحلیل‌های Pareto
            </h1>
            <p style="color:#6b7280; margin-top:4px; font-size:0.875rem;">
                شناسایی ۲۰٪ عوامل حیاتی که ۸۰٪ مشکلات را ایجاد می‌کنند
            </p>
        </div>
        <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=create" class="qc-btn-primary">
            <i class="fas fa-plus"></i> تحلیل جدید
        </a>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="qc-alert qc-alert-success qc-mb-3">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="qc-alert qc-alert-danger qc-mb-3">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="qc-stats-grid qc-mb-4">
        <div class="qc-stat-card">
            <div class="qc-stat-icon" style="background:#e0f2fe; color:#0369a1;">
                <i class="fas fa-chart-bar"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)($stats['total'] ?? 0) ?></div>
                <div class="qc-stat-label">کل تحلیل‌ها</div>
            </div>
        </div>
        <div class="qc-stat-card">
            <div class="qc-stat-icon" style="background:#fef3c7; color:#b45309;">
                <i class="fas fa-hourglass-half"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)(($stats['draft'] ?? 0) + ($stats['analyzed'] ?? 0)) ?></div>
                <div class="qc-stat-label">در جریان</div>
            </div>
        </div>
        <div class="qc-stat-card success">
            <div class="qc-stat-icon" style="background:#d1fae5; color:#047857;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= (int)(($stats['action_taken'] ?? 0) + ($stats['closed'] ?? 0)) ?></div>
                <div class="qc-stat-label">تکمیل‌شده</div>
            </div>
        </div>
        <div class="qc-stat-card info">
            <div class="qc-stat-icon" style="background:#e0e7ff; color:#4338ca;">
                <i class="fas fa-sum"></i>
            </div>
            <div>
                <div class="qc-stat-value"><?= number_format((float)($stats['total_value'] ?? 0), 0) ?></div>
                <div class="qc-stat-label">مجموع مقادیر</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="qc-card qc-mb-3">
        <div class="qc-card-body">
            <form method="GET" class="qc-flex qc-gap-2" style="flex-wrap:wrap; align-items:flex-end;">
                <input type="hidden" name="controller" value="pareto">
                <div style="flex:1; min-width:200px;">
                    <label class="qc-form-label">جستجو</label>
                    <input type="text" name="search" class="qc-form-control"
                           value="<?= htmlspecialchars($filters['search']) ?>"
                           placeholder="عنوان یا شرح مسئله...">
                </div>
                <div style="min-width:150px;">
                    <label class="qc-form-label">وضعیت</label>
                    <select name="status" class="qc-form-select">
                        <option value="">همه</option>
                        <?php foreach ($statusLabels as $key => $meta): ?>
                            <option value="<?= $key ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                <?= $meta['label'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="min-width:200px;">
                    <label class="qc-form-label">پروژه</label>
                    <select name="project_id" class="qc-form-select">
                        <option value="">همه پروژه‌ها</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= (int)$filters['project_id'] === (int)$p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="qc-btn-primary">
                    <i class="fas fa-filter"></i> اعمال
                </button>
                <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto" class="qc-btn-outline">
                    <i class="fas fa-times"></i> پاک‌سازی
                </a>
            </form>
        </div>
    </div>

    <!-- List -->
    <?php if (empty($analyses)): ?>
        <div class="qc-empty-state">
            <i class="fas fa-chart-bar" style="font-size:3rem; color:#cbd5e1;"></i>
            <h3 style="margin-top:1rem; color:#64748b;">هنوز تحلیل Pareto نداری</h3>
            <p style="color:#94a3b8; margin-bottom:1.5rem;">
                اولین تحلیل رو بساز تا ۲۰٪ عوامل حیاتی رو شناسایی کنی.
            </p>
            <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=create" class="qc-btn-primary">
                <i class="fas fa-plus"></i> شروع تحلیل جدید
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
                            <th>پروژه</th>
                            <th>تاریخ</th>
                            <th>مجموع</th>
                            <th>Vital Few</th>
                            <th>وضعیت</th>
                            <th style="width:160px;">عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analyses as $i => $a): ?>
                            <?php
                                $projectName = '';
                                foreach ($projects as $p) {
                                    if ((int)$p['id'] === (int)$a['project_id']) {
                                        $projectName = $p['name'];
                                        break;
                                    }
                                }
                                $statusMeta = $statusLabels[$a['status']] ?? ['label' => $a['status'], 'class' => 'info'];
                            ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $a['id'] ?>"
                                       style="color:#059669; font-weight:600; text-decoration:none;">
                                        <?= htmlspecialchars($a['title']) ?>
                                    </a>
                                    <?php if ($a['problem_statement']): ?>
                                        <div style="font-size:0.75rem; color:#94a3b8; margin-top:2px;">
                                            <?= htmlspecialchars(mb_substr($a['problem_statement'], 0, 60)) ?>
                                            <?= mb_strlen($a['problem_statement']) > 60 ? '…' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?= $projectName ? htmlspecialchars($projectName) : '—' ?></td>
                                <td style="font-size:0.85rem; color:#64748b;">
                                    <?= htmlspecialchars($a['analysis_date'] ?? '—') ?>
                                </td>
                                <td>
                                    <?= number_format((float)$a['total_value'], 0) ?>
                                    <?php if ($a['unit']): ?>
                                        <span style="font-size:0.75rem; color:#94a3b8;">
                                            <?= htmlspecialchars($a['unit']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="qc-chart-badge">
                                        <?= (int)$a['vital_few_count'] ?> دسته
                                    </span>
                                    <span style="font-size:0.75rem; color:#94a3b8; margin-left:4px;">
                                        (<?= number_format((float)$a['vital_few_percent'], 1) ?>٪)
                                    </span>
                                </td>
                                <td>
                                    <span class="qc-status-badge qc-status-<?= $statusMeta['class'] ?>">
                                        <?= $statusMeta['label'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:6px;">
                                        <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=show&id=<?= $a['id'] ?>"
                                           class="qc-btn-outline" style="padding:4px 10px; font-size:0.8rem;"
                                           title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=edit&id=<?= $a['id'] ?>"
                                           class="qc-btn-outline" style="padding:4px 10px; font-size:0.8rem;"
                                           title="ویرایش">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button"
                                                class="qc-btn-danger qc-confirm-delete"
                                                style="padding:4px 10px; font-size:0.8rem;"
                                                data-url="<?= CURRENT_MODULE_URL ?>?controller=pareto&action=delete"
                                                data-id="<?= $a['id'] ?>"
                                                data-message="این تحلیل و تمام آیتم‌های آن حذف خواهند شد. مطمئنی؟"
                                                title="حذف">
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