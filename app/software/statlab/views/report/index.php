<?php
/**
 * StatLab - لیست پروژه‌های دارای گزارش
 */
$stats = $stats ?? ['with_report' => 0, 'results' => 0, 'sig' => 0, 'datasets' => 0];
$reportProjects = $reportProjects ?? [];
$catLabels = ['descriptive' => 'آمار توصیفی', 'distribution' => 'توزیع‌ها', 'hypothesis' => 'آزمون فرض', 'regression' => 'رگرسیون'];
$catColors = ['descriptive' => 'primary', 'distribution' => 'info', 'hypothesis' => 'warning', 'regression' => 'success'];
?>
<div class="container-fluid py-3 py-md-4">

    <!-- ═══ هدر ═══ -->
    <div class="statlab-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-chart-bar text-success me-2"></i>گزارش‌های StatLab</h3>
            <small class="text-muted">فهرست پروژه‌هایی که حداقل یک تحلیل انجام شده دارند</small>
        </div>
        <div class="statlab-actions statlab-no-print">
            <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-home me-1"></i><span class="d-none d-md-inline">داشبورد</span>
            </a>
        </div>
    </div>

    <!-- ═══ کارت‌های آماری ═══ -->
    <div class="row g-2 g-md-3 mb-3 mb-md-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">پروژه دارای گزارش</small>
                <h4 class="mb-0 text-primary"><?= (int)$stats['with_report'] ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">کل تحلیل‌ها</small>
                <h4 class="mb-0 text-success"><?= (int)$stats['results'] ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">معنادار (p&lt;0.05)</small>
                <h4 class="mb-0 text-warning"><?= (int)$stats['sig'] ?></h4>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm text-center p-3">
                <small class="text-muted d-block">مجموعه داده‌ها</small>
                <h4 class="mb-0 text-info"><?= (int)$stats['datasets'] ?></h4>
            </div>
        </div>
    </div>

    <!-- ═══ لیست گزارش‌ها ═══ -->
    <?php if (empty($reportProjects)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">هنوز گزارشی ثبت نشده است</h5>
                <p class="text-muted small">پس از انجام اولین تحلیل، گزارش پروژه‌ها اینجا نمایش داده می‌شود.</p>
                <a href="<?= stat_url('controller=descriptive') ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i> شروع اولین تحلیل
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($reportProjects as $rp): ?>
            <div class="card border-0 shadow-sm mb-2 statlab-report-row">
                <div class="card-body p-3 d-flex flex-column flex-md-row align-items-md-center gap-3">
                    <!-- اطلاعات پروژه -->
                    <div class="flex-grow-1" style="min-width: 0;">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <h6 class="fw-bold mb-0">
                                <i class="fas fa-file-invoice me-1 text-success"></i>
                                <?= stat_e($rp['name']) ?>
                            </h6>
                            <span class="badge bg-<?= $catColors[$rp['category_code']] ?? 'secondary' ?>">
                                <?= $catLabels[$rp['category_code']] ?? stat_e($rp['category_code']) ?>
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 small text-muted">
                            <span><i class="fas fa-chart-bar me-1"></i><?= (int)$rp['result_count'] ?> تحلیل</span>
                            <?php if ((int)$rp['sig_count'] > 0): ?>
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i><?= (int)$rp['sig_count'] ?> معنادار</span>
                            <?php endif; ?>
                            <span><i class="fas fa-sliders-h me-1"></i>α=<?= number_format((float)$rp['significance_level'], 2) ?></span>
                            <span><i class="fas fa-clock me-1"></i><?= stat_e($rp['last_analysis']) ?></span>
                        </div>
                    </div>
                    <!-- دکمه گزارش (هرگز فشرده نمی‌شود) -->
                    <a href="<?= stat_url('controller=report&action=show&id=' . (int)$rp['id']) ?>"
                       class="btn btn-sm btn-success w-100 w-md-auto text-nowrap flex-shrink-0">
                        <i class="fas fa-eye me-1"></i> مشاهده گزارش کامل
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>