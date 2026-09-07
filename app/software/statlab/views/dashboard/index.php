<?php
/**
 * StatLab - داشبورد (نسخه بازنویسی‌شده، ریسپانسیو و پایدار)
 */
$stats = $stats ?? [];
$recentProjects = $recentProjects ?? [];

$quickLinks = [
    ['url' => 'controller=descriptive',        'icon' => 'fas fa-chart-simple',      'color' => '#0d6efd', 'label' => 'آمار توصیفی',        'desc' => 'میانگین، میانه، چولگی، چهارک‌ها و شناسایی داده پرت'],
    ['url' => 'controller=distribution',       'icon' => 'fas fa-dice',              'color' => '#6f42c1', 'label' => 'توزیع‌های احتمال',     'desc' => 'نرمال، t، کای‌دو، F، پواسون، دوجمله‌ای و ویبول'],
    ['url' => 'controller=hypothesis',         'icon' => 'fas fa-scale-balanced',    'color' => '#fd7e14', 'label' => 'آزمون فرض',            'desc' => 't تک/دو نمونه، جفتی، Z نسبت، کای‌دو، من-ویتنی'],
    ['url' => 'controller=regression',         'icon' => 'fas fa-chart-line',        'color' => '#198754', 'label' => 'رگرسیون و همبستگی',    'desc' => 'پیرسون/اسپیرمن، خطی ساده و چندگانه با نمودار'],
    ['url' => 'controller=smart_statistician', 'icon' => 'fas fa-wand-magic-sparkles','color' => '#d63384', 'label' => 'دستیار هوشمند',        'desc' => 'تشخیص خودکار آزمون مناسب از متن فارسی'],
    ['url' => 'controller=project',            'icon' => 'fas fa-folder-open',       'color' => '#6c757d', 'label' => 'پروژه‌های آماری',      'desc' => 'مدیریت پروژه‌ها، متغیرها و مجموعه داده‌ها'],
    ['url' => 'controller=report',             'icon' => 'fas fa-chart-bar',         'color' => '#20c997', 'label' => 'گزارش‌ها',             'desc' => 'گزارش پروژه‌محور قابل چاپ و خروجی PDF'],
];
$catLabels = ['descriptive' => 'توصیفی', 'distribution' => 'توزیع', 'hypothesis' => 'آزمون فرض', 'regression' => 'رگرسیون'];
?>
<div class="container-fluid py-3 py-md-4">

    <!-- ═══ هدر ═══ -->
    <div class="statlab-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-gauge-high text-success me-2"></i>داشبورد StatLab</h3>
            <small class="text-muted">مرکز تحلیل‌های آماری مهندسی صنایع</small>
        </div>
        <a href="<?= stat_url('controller=smart_statistician') ?>" class="btn btn-success btn-sm text-nowrap">
            <i class="fas fa-wand-magic-sparkles me-1"></i> شروع تحلیل هوشمند
        </a>
    </div>

    <!-- ═══ کارت‌های آماری ═══ -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">پروژه‌های من</small>
                <h4 class="mb-0 text-primary"><?= (int)($stats['total_projects'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">تکمیل شده</small>
                <h4 class="mb-0 text-success"><?= (int)($stats['completed'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">مجموعه داده‌ها</small>
                <h4 class="mb-0 text-info"><?= (int)($stats['total_datasets'] ?? 0) ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">تحلیل‌های انجام‌شده</small>
                <h4 class="mb-0 text-warning"><?= (int)($stats['total_results'] ?? 0) ?></h4>
            </div>
        </div>
    </div>

    <!-- ═══ دسترسی سریع (کارت داخل لینک، با card-body استاندارد) ═══ -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <?php foreach ($quickLinks as $q): ?>
            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                <a href="<?= stat_url($q['url']) ?>" class="text-decoration-none d-block h-100">
                    <div class="card border-0 shadow-sm h-100 statlab-quick-card">
                        <div class="card-body p-3 text-center">
                            <i class="<?= $q['icon'] ?> fa-2x mb-2 d-block" style="color:<?= $q['color'] ?>;"></i>
                            <h6 class="fw-bold mb-1"><?= $q['label'] ?></h6>
                            <small class="text-muted d-block"><?= $q['desc'] ?></small>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ═══ آخرین پروژه‌ها ═══ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 py-md-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-clock-rotate-left me-2"></i>آخرین پروژه‌ها</h6>
            <a href="<?= stat_url('controller=project') ?>" class="btn btn-sm btn-outline-secondary">همه پروژه‌ها</a>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recentProjects)): ?>
                <div class="text-center text-muted py-4 small">
                    هنوز پروژه‌ای ایجاد نکرده‌اید.
                    <a href="<?= stat_url('controller=descriptive') ?>" class="alert-link">اولین تحلیل را شروع کنید</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>پروژه</th>
                                <th class="d-none d-md-table-cell">دسته</th>
                                <th>وضعیت</th>
                                <th class="d-none d-sm-table-cell">به‌روزرسانی</th>
                                <th class="text-center">عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProjects as $p): ?>
                                <tr>
                                    <td class="fw-bold"><?= stat_e($p['name']) ?></td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-light text-dark border"><?= $catLabels[$p['category_code']] ?? stat_e($p['category_code']) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $p['status'] === 'completed' ? 'success' : 'secondary' ?>">
                                            <?= stat_getStatusLabel($p['status']) ?>
                                        </span>
                                    </td>
                                    <td class="d-none d-sm-table-cell text-muted"><?= stat_e($p['updated_at']) ?></td>
                                    <td class="text-center">
                                        <a href="<?= stat_url('controller=project&action=show&id=' . (int)$p['id']) ?>"
                                           class="btn btn-sm btn-outline-success py-0">
                                            <i class="fas fa-eye"></i>
                                        </a>
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