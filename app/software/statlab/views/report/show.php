<?php
/**
 * StatLab - گزارش تفصیلی یک پروژه (ریسپانسیو + قابل چاپ)
 */
$project = $project ?? [];
$results = $results ?? [];
$datasets = $datasets ?? [];
$catLabels = ['descriptive' => 'آمار توصیفی', 'distribution' => 'توزیع‌ها', 'hypothesis' => 'آزمون فرض', 'regression' => 'رگرسیون'];

$sigCount = count(array_filter($results, fn($r) => $r['p_value'] !== null && (float)$r['p_value'] < 0.05));
?>
<div class="container-fluid py-3 py-md-4">

    <!-- ═══ هدر ═══ -->
    <div class="statlab-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-file-invoice text-success me-2"></i><?= stat_e($project['name']) ?></h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= stat_url('controller=report') ?>">گزارش‌ها</a></li>
                    <li class="breadcrumb-item active"><?= stat_e(mb_substr($project['name'], 0, 30)) ?></li>
                </ol>
            </nav>
        </div>
        <div class="statlab-actions statlab-no-print">
            <button type="button" class="btn btn-outline-dark btn-sm" onclick="window.print()">
                <i class="fas fa-print me-1"></i> چاپ / PDF
            </button>
            <a href="<?= stat_url('controller=project&action=show&id=' . (int)$project['id']) ?>" class="btn btn-outline-success btn-sm">
                <i class="fas fa-folder-open me-1"></i><span class="d-none d-md-inline">صفحه پروژه</span>
            </a>
            <a href="<?= stat_url('controller=report') ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
            </a>
        </div>
    </div>

    <!-- ═══ اطلاعات پروژه ═══ -->
    <div class="card border-0 shadow-sm mb-3 mb-md-4 statlab-report-group">
        <div class="card-body p-3 p-md-4">
            <div class="row g-2 g-md-3 text-center text-md-start mb-2">
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">دسته تحلیل</small>
                    <span class="badge bg-primary fs-6"><?= $catLabels[$project['category_code']] ?? stat_e($project['category_code']) ?></span>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">سطح α</small>
                    <strong><?= number_format((float)$project['significance_level'], 3) ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">تعداد تحلیل‌ها</small>
                    <strong><?= count($results) ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">نتایج معنادار</small>
                    <strong class="<?= $sigCount > 0 ? 'text-success' : 'text-muted' ?>"><?= $sigCount ?></strong>
                </div>
            </div>

            <?php if (!empty($project['description'])): ?>
                <hr class="my-2 my-md-3">
                <p class="small text-muted mb-2"><?= nl2br(stat_e(mb_substr($project['description'], 0, 500))) ?></p>
            <?php endif; ?>

            <?php if (!empty($datasets)): ?>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <small class="text-muted align-self-center me-1"><i class="fas fa-database me-1"></i> متغیرها:</small>
                    <?php foreach ($datasets as $d):
                        $dData = json_decode($d['data_json'] ?? '[]', true) ?: [];
                    ?>
                        <span class="badge bg-light text-dark border">
                            <?= stat_e($d['name']) ?> (<?= count($dData) ?>)
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- خلاصه خودکار -->
            <div class="alert alert-light border py-2 small mb-0 mt-3">
                <i class="fas fa-robot me-1 text-success"></i>
                <strong>خلاصه خودکار:</strong>
                در این پروژه <?= count($results) ?> تحلیل انجام شده است
                <?php if ($sigCount > 0): ?>
                    که <?= $sigCount ?> مورد از نظر آماری معنادار بوده‌اند (p &lt; 0.05).
                <?php else: ?>
                    و هیچ‌کدام معنادار نبوده‌اند (یا p-value ثبت نشده است).
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══ نتایج تفصیلی ═══ -->
    <?php foreach ($results as $r):
        $out = json_decode($r['output_data'] ?? '{}', true) ?: [];

        $metrics = [];
        if (isset($out['count']))        $metrics['تعداد'] = number_format((float)$out['count'], 0);
        if (isset($out['mean']))         $metrics['میانگین'] = number_format((float)$out['mean'], 4);
        if (isset($out['median']))       $metrics['میانه'] = number_format((float)$out['median'], 4);
        if (isset($out['std']))          $metrics['انحراف معیار'] = number_format((float)$out['std'], 4);
        if (isset($out['pearson_r']))    $metrics['پیرسون r'] = number_format((float)$out['pearson_r'], 4);
        if (isset($out['spearman_rho'])) $metrics['اسپیرمن ρ'] = number_format((float)$out['spearman_rho'], 4);
        if (isset($out['r2']))           $metrics['R²'] = number_format((float)$out['r2'] * 100, 1) . '%';
        if (isset($out['slope']))        $metrics['شیب'] = number_format((float)$out['slope'], 4);
        if (isset($out['F']))            $metrics['F'] = number_format((float)$out['F'], 3);
        if (isset($out['statistic']))    $metrics['آماره ' . ($out['statistic_name'] ?? '')] = number_format((float)$out['statistic'], 4);
        if (isset($out['coefficients'])) {
            $sig = count(array_filter($out['coefficients'], fn($c) => !empty($c['sig'])));
            $metrics['ضرایب معنادار'] = $sig . ' از ' . count($out['coefficients']);
        }
        $metrics = array_slice($metrics, 0, 6, true);
        $pval = $r['p_value'] !== null ? (float)$r['p_value'] : null;
    ?>
        <div class="card border-0 shadow-sm mb-3 statlab-report-group">
            <div class="card-header bg-white py-2 py-md-3">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-line me-1 text-primary"></i>
                        <?= stat_e($r['method_name']) ?>
                        <?php if (!empty($r['dataset_name'])): ?>
                            <small class="text-muted">| متغیر: <?= stat_e($r['dataset_name']) ?></small>
                        <?php endif; ?>
                    </h6>
                    <div class="d-flex flex-wrap gap-1">
                        <?php if ($pval !== null): ?>
                            <span class="badge <?= $pval < 0.05 ? 'bg-success' : 'bg-secondary' ?>">
                                p = <?= $pval < 0.000001 ? number_format($pval, 1, '.', 'e') : number_format($pval, 5) ?>
                                <?= $pval < 0.05 ? '(معنادار)' : '(نامعنادر)' ?>
                            </span>
                        <?php endif; ?>
                        <small class="text-muted align-self-center"><?= stat_e($r['created_at']) ?></small>
                    </div>
                </div>
            </div>
            <div class="card-body p-3 p-md-4">
                <?php if (!empty($metrics)): ?>
                    <div class="row g-1 g-md-2 mb-3">
                        <?php foreach ($metrics as $label => $value): ?>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="stat-box">
                                    <small><?= stat_e($label) ?></small>
                                    <strong class="small"><?= $value ?></strong>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($r['interpretation_fa'])): ?>
                    <pre class="small mb-0" style="white-space: pre-wrap; color:#495057;"><?= stat_e($r['interpretation_fa']) ?></pre>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- ═══ پایان گزارش ═══ -->
    <div class="text-center text-muted small py-3 statlab-no-print">
        <i class="fas fa-check-circle me-1 text-success"></i> پایان گزارش — تولیدشده توسط StatLab Analyzer
    </div>
</div>