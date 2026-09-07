<?php
/**
 * StatLab - نمایش جزئیات پروژه (نسخه نهایی با مودال مستقل)
 * مسیر: app/software/statlab/views/project/show.php
 */
$activeTab = $_GET['tab'] ?? 'datasets';
$datasetData = $datasetData ?? [];
?>
<div class="container-fluid py-3 py-md-4">

    <!-- ═══ هدر صفحه ═══ -->
    <div class="statlab-page-header">
        <div class="text-center text-md-start">
            <h3 class="mb-1">
                <i class="fas fa-chart-line text-success me-2"></i><?= stat_e($project['name']) ?>
            </h3>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 justify-content-center justify-content-md-start">
                    <li class="breadcrumb-item"><a href="<?= stat_url('controller=project') ?>">پروژه‌ها</a></li>
                    <li class="breadcrumb-item active"><?= stat_e(mb_substr($project['name'], 0, 25)) ?></li>
                </ol>
            </nav>
        </div>
        <div class="statlab-actions">
            <a href="<?= stat_url('controller=descriptive&project_id=' . $project['id']) ?>" class="btn btn-success btn-sm">
                <i class="fas fa-plus me-1"></i><span class="d-none d-md-inline">افزودن داده جدید</span><span class="d-md-none">داده جدید</span>
            </a>
            <a href="<?= stat_url('controller=project&action=edit&id=' . $project['id']) ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-edit me-1"></i><span class="d-none d-md-inline">ویرایش</span>
            </a>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteProject(<?= $project['id'] ?>)">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
        </div>
    </div>

    <!-- ═══ کارت اطلاعات کلی ═══ -->
    <div class="card border-0 shadow-sm mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="row g-2 g-md-3 text-center text-md-start">
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">وضعیت</small>
                    <span class="badge bg-<?= $project['status'] === 'completed' ? 'success' : 'secondary' ?> fs-6">
                        <?= stat_getStatusLabel($project['status']) ?>
                    </span>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">دسته</small>
                    <strong class="small"><?= stat_e($project['category_code'] ?? '-') ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">سطح α</small>
                    <strong class="small"><?= number_format($project['significance_level'], 3) ?></strong>
                </div>
                <div class="col-6 col-lg-3">
                    <small class="text-muted d-block">به‌روزرسانی</small>
                    <strong class="small"><?= stat_e($project['updated_at']) ?></strong>
                </div>
            </div>
            <?php if (!empty($project['description'])): ?>
                <hr class="my-2 my-md-3">
                <p class="mb-0 small"><?= nl2br(stat_e($project['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ═══ تب‌ها (اسکرول‌پذیر در موبایل) ═══ -->
    <ul class="nav nav-tabs statlab-tabs mb-3 mb-md-4" role="tablist">
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'datasets' ? 'active' : '' ?>"
               href="<?= stat_url('controller=project&action=show&id=' . $project['id'] . '&tab=datasets') ?>">
                <i class="fas fa-database me-1"></i> داده‌ها (<?= count($datasets) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'results' ? 'active' : '' ?>"
               href="<?= stat_url('controller=project&action=show&id=' . $project['id'] . '&tab=results') ?>">
                <i class="fas fa-chart-bar me-1"></i> نتایج (<?= count($results) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $activeTab === 'info' ? 'active' : '' ?>"
               href="<?= stat_url('controller=project&action=show&id=' . $project['id'] . '&tab=info') ?>">
                <i class="fas fa-info-circle me-1"></i> تکمیلی
            </a>
        </li>
    </ul>

    <!-- ═══ تب داده‌ها ═══ -->
    <?php if ($activeTab === 'datasets'): ?>
        <?php if (empty($datasets)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                هنوز داده‌ای برای این پروژه ثبت نشده است.
                <a href="<?= stat_url('controller=descriptive&project_id=' . $project['id']) ?>" class="alert-link">افزودن داده جدید</a>
            </div>
        <?php else: ?>
            <div class="row g-2 g-md-3">
                <?php foreach ($datasets as $idx => $d):
                    $dData = json_decode($d['data_json'] ?? '[]', true) ?: [];
                ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-2">
                                    <span class="badge bg-primary me-1">x<?= $idx + 1 ?></span>
                                    <?= stat_e($d['name']) ?>
                                </h6>
                                <div class="text-muted small mb-3">
                                    <div><i class="fas fa-users me-1"></i> <?= (int)$d['sample_size'] ?> مشاهده</div>
                                    <?php if (count($dData) > 0): ?>
                                        <div class="mt-1">
                                            <i class="fas fa-ruler me-1"></i>
                                            <?= number_format(min($dData), 2) ?> تا <?= number_format(max($dData), 2) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100"
                                        onclick="showDatasetPreview(<?= (int)$d['id'] ?>)">
                                    <i class="fas fa-eye me-1"></i> پیش‌نمایش داده‌ها
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- ═══ تب نتایج ═══ -->
        <?php elseif ($activeTab === 'results'): ?>
            <?php if (empty($results)): ?>
                <div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>هنوز نتیجه‌ای ثبت نشده است.</div>
            <?php else: ?>
                <?php foreach ($results as $r):
                    $out = json_decode($r['output_data'] ?? '{}', true) ?: [];

                    // ✅ ادغام آماره‌های داخل extra (مربوط به آزمون‌های فرض)
                    if (!empty($out['extra']) && is_array($out['extra'])) {
                        $out = array_merge($out, $out['extra']);
                    }
                    if (isset($out['n']) && !isset($out['count'])) $out['count'] = $out['n'];

                    // ✅ اگر میانگین/میانه/انحراف در خروجی نیست، از دادهٔ متصل به نتیجه محاسبه کن
                    if (!isset($out['mean'])) {
                        $vals = $datasetData[(int)($r['dataset_id'] ?? 0)] ?? [];
                        $n = count($vals);
                        if ($n >= 1) {
                            $mean = array_sum($vals) / $n;
                            $sorted = $vals; sort($sorted);
                            $mid = intdiv($n, 2);
                            $median = $n % 2 ? $sorted[$mid] : ($sorted[$mid - 1] + $sorted[$mid]) / 2;
                            $ss = 0;
                            foreach ($vals as $v) $ss += ($v - $mean) ** 2;
                            $out['count']  = $out['count'] ?? $n;
                            $out['mean']   = $mean;
                            $out['median'] = $median;
                            $out['std']    = $n > 1 ? sqrt($ss / ($n - 1)) : 0;
                        }
                    }

                    // ✅ شاخص‌های شرطی متناسب با نوع تحلیل
                    $metrics = [];
                    if (isset($out['count']))        $metrics['تعداد'] = number_format((float)$out['count'], 0);
                    if (isset($out['mean']))         $metrics['میانگین'] = number_format((float)$out['mean'], 4);
                    if (isset($out['median']))       $metrics['میانه'] = number_format((float)$out['median'], 4);
                    if (isset($out['std']))          $metrics['انحراف معیار'] = number_format((float)$out['std'], 4);
                    if (isset($out['pearson_r']))    $metrics['پیرسون r'] = number_format((float)$out['pearson_r'], 4);
                    if (isset($out['spearman_rho'])) $metrics['اسپیرمن ρ'] = number_format((float)$out['spearman_rho'], 4);
                    if (isset($out['r2']))           $metrics['R²'] = number_format((float)$out['r2'] * 100, 1) . '%';
                    if (isset($out['slope']))        $metrics['شیب'] = number_format((float)$out['slope'], 4);
                    if (isset($out['intercept']))    $metrics['عرض از مبدأ'] = number_format((float)$out['intercept'], 4);
                    if (isset($out['F']))            $metrics['F'] = number_format((float)$out['F'], 3);
                    if (isset($out['statistic']))    $metrics['آماره ' . ($out['statistic_name'] ?? '')] = number_format((float)$out['statistic'], 4);
                    if (isset($out['coefficients'])) {
                        $sig = count(array_filter($out['coefficients'], fn($c) => !empty($c['sig'])));
                        $metrics['ضرایب معنادار'] = $sig . ' از ' . count($out['coefficients']);
                    }
                    $metrics = array_slice($metrics, 0, 8, true);

                    $pval = $r['p_value'] !== null ? (float)$r['p_value'] : null;
                ?>
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white py-2 py-md-3">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-1">
                                <h6 class="mb-0 small">
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
                        <div class="card-body p-3">
                            <?php if (!empty($metrics)): ?>
                                <div class="row g-1 g-md-2 mb-2">
                                    <?php foreach ($metrics as $label => $value): ?>
                                        <div class="col-6 col-md-4 col-lg-3">
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
            <?php endif; ?>

    <!-- ═══ تب اطلاعات تکمیلی ═══ -->
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <div class="table-responsive">
                    <table class="table table-borderless mb-0 small">
                        <tr><td class="fw-bold" style="min-width:160px;">شناسه پروژه:</td><td>#<?= (int)$project['id'] ?></td></tr>
                        <tr><td class="fw-bold">هدف تحلیل:</td><td><?= stat_e($project['objective'] ?? '-') ?></td></tr>
                        <tr><td class="fw-bold">نوع تحلیل:</td><td><?= stat_e($project['analysis_type_code'] ?? '-') ?></td></tr>
                        <tr><td class="fw-bold">تاریخ ایجاد:</td><td><?= stat_e($project['created_at']) ?></td></tr>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════
     مودال مستقل StatLab (بدون وابستگی به Bootstrap JS)
     ═══════════════════════════════════════════════════════ -->
<div id="statlabModal"
     style="display:none; position:fixed; inset:0; z-index:1050;
            background:rgba(0,0,0,.5); align-items:center; justify-content:center;
            padding: 1rem;">
    <div style="background:#fff; border-radius:.5rem; width:100%; max-width:800px;
                max-height:90vh; display:flex; flex-direction:column;
                box-shadow:0 10px 40px rgba(0,0,0,.3);">
        <!-- سربرگ مودال -->
        <div style="display:flex; align-items:center; justify-content:space-between;
                    padding:.75rem 1rem; border-bottom:1px solid #dee2e6; flex-shrink:0;">
            <h5 id="statlabModalTitle" style="margin:0; font-size:1.1rem;"></h5>
            <button type="button" onclick="closeStatlabModal()"
                    style="background:none; border:none; font-size:1.5rem;
                           line-height:1; cursor:pointer; color:#6c757d; padding:0 .25rem;"
                    aria-label="بستن">&times;</button>
        </div>
        <!-- محتوای مودال -->
        <div id="statlabModalBody"
             style="padding:1rem; overflow-y:auto; flex:1 1 auto;"></div>
        <!-- پاورقی مودال -->
        <div style="display:flex; justify-content:flex-end; gap:.5rem;
                    padding:.75rem 1rem; border-top:1px solid #dee2e6; flex-shrink:0;">
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    onclick="copyModalContent()">
                <i class="fas fa-copy me-1"></i> کپی
            </button>
            <button type="button" class="btn btn-sm btn-secondary"
                    onclick="closeStatlabModal()">
                بستن
            </button>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════
// داده‌های تعبیه‌شده از PHP
// ═══════════════════════════════════════════════════════
const DATASETS = {};
<?php foreach ($datasets as $d): ?>
DATASETS[<?= (int)$d['id'] ?>] = {
    name: <?= json_encode($d['name'], JSON_UNESCAPED_UNICODE) ?>,
    data: <?= json_encode(json_decode($d['data_json'] ?? '[]', true) ?: []) ?>,
    created: <?= json_encode($d['created_at'] ?? '') ?>
};
<?php endforeach; ?>

const RESULTS = {};
<?php foreach ($results as $r): ?>
RESULTS[<?= (int)$r['id'] ?>] = {
    method: <?= json_encode($r['method_name'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    dataset: <?= json_encode($r['dataset_name'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
    output: <?= json_encode(json_decode($r['output_data'] ?? '{}', true) ?: [], JSON_UNESCAPED_UNICODE) ?>,
    interpretation: <?= json_encode($r['interpretation_fa'] ?? '', JSON_UNESCAPED_UNICODE) ?>
};
<?php endforeach; ?>

let currentModalRaw = '';

// ═══════════════════════════════════════════════════════
// ابزارهای کمکی
// ═══════════════════════════════════════════════════════
function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = String(s ?? '');
    return div.innerHTML;
}

function statBox(label, value) {
    return `<div class="col-6 col-md-3"><div class="stat-box"><small>${escapeHtml(label)}</small><strong>${escapeHtml(value)}</strong></div></div>`;
}

// ═══════════════════════════════════════════════════════
// توابع باز/بسته کردن مودال (کاملاً مستقل)
// ═══════════════════════════════════════════════════════
function openStatlabModal() {
    const modal = document.getElementById('statlabModal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeStatlabModal() {
    const modal = document.getElementById('statlabModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
}

// بستن با کلیک روی backdrop
document.getElementById('statlabModal').addEventListener('click', function(e) {
    if (e.target === this) closeStatlabModal();
});

// بستن با کلید Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('statlabModal').style.display === 'flex') {
        closeStatlabModal();
    }
});

// ═══════════════════════════════════════════════════════
// پیش‌نمایش داده‌های یک dataset
// ═══════════════════════════════════════════════════════
function showDatasetPreview(id) {
    const ds = DATASETS[id];
    if (!ds) { alert('داده‌ای یافت نشد.'); return; }

    const vals = Array.isArray(ds.data) ? ds.data : [];
    const n = vals.length;
    const mean = n ? vals.reduce((a, b) => a + b, 0) / n : 0;
    const min = n ? Math.min(...vals) : 0;
    const max = n ? Math.max(...vals) : 0;

    document.getElementById('statlabModalTitle').innerHTML =
        `<i class="fas fa-table me-2 text-primary"></i>${escapeHtml(ds.name)} <span class="badge bg-secondary ms-1">${n} داده</span>`;

    let html = '<div class="row g-1 g-md-2 mb-3">' +
        statBox('تعداد', n) +
        statBox('میانگین', mean.toFixed(4)) +
        statBox('کمینه', Number(min).toFixed(2)) +
        statBox('بیشینه', Number(max).toFixed(2)) +
        '</div>';

    if (n > 0) {
        html += '<div class="statlab-scroll-y table-responsive">' +
                '<table class="table table-sm table-striped mb-0 small">' +
                '<thead class="table-light"><tr><th style="width:60px;">#</th><th>مقدار</th></tr></thead><tbody>';
        vals.forEach((v, i) => {
            html += `<tr><td class="text-muted">${i + 1}</td><td class="font-monospace">${Number(v)}</td></tr>`;
        });
        html += '</tbody></table></div>';
    } else {
        html += '<div class="alert alert-warning small mb-0">این مجموعه داده خالی است.</div>';
    }

    document.getElementById('statlabModalBody').innerHTML = html;
    currentModalRaw = vals.join('\n');
    openStatlabModal();
}

// ═══════════════════════════════════════════════════════
// نمایش JSON کامل یک نتیجه
// ═══════════════════════════════════════════════════════
function showResultDetails(id) {
    const r = RESULTS[id];
    if (!r) { alert('نتیجه‌ای یافت نشد.'); return; }

    document.getElementById('statlabModalTitle').innerHTML =
        `<i class="fas fa-code me-2 text-info"></i>${escapeHtml(r.method)} <small class="text-muted">(${escapeHtml(r.dataset || '')})</small>`;

    let html = '';
    if (r.interpretation) {
        html += `<div class="alert alert-info py-2 mb-3">
                    <h6 class="fw-bold mb-1 small"><i class="fas fa-lightbulb me-1"></i> تفسیر:</h6>
                    <pre class="mb-0 small" style="white-space: pre-wrap;">${escapeHtml(r.interpretation)}</pre>
                 </div>`;
    }
    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-code me-1"></i> خروجی کامل (JSON):</h6>`;
    html += `<pre class="bg-light p-2 p-md-3 rounded border statlab-scroll-y"
                 style="direction: ltr; text-align: left; font-size: 0.75rem; margin-bottom:0;">` +
            escapeHtml(JSON.stringify(r.output, null, 2)) + `</pre>`;

    document.getElementById('statlabModalBody').innerHTML = html;
    currentModalRaw = JSON.stringify(r.output, null, 2);
    openStatlabModal();
}

// ═══════════════════════════════════════════════════════
// کپی محتوا به کلیپ‌بورد
// ═══════════════════════════════════════════════════════
async function copyModalContent() {
    if (!currentModalRaw) {
        alert('محتوایی برای کپی وجود ندارد.');
        return;
    }
    try {
        await navigator.clipboard.writeText(currentModalRaw);
        alert('✅ در کلیپ‌بورد کپی شد.');
    } catch (e) {
        // Fallback برای مرورگرهای قدیمی
        const ta = document.createElement('textarea');
        ta.value = currentModalRaw;
        ta.style.position = 'fixed';
        ta.style.top = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
            alert('✅ در کلیپ‌بورد کپی شد.');
        } catch (err) {
            alert('❌ کپی خودکار ممکن نیست. متن را دستی انتخاب کنید.');
        }
        document.body.removeChild(ta);
    }
}

// ═══════════════════════════════════════════════════════
// حذف پروژه
// ═══════════════════════════════════════════════════════
function deleteProject(id) {
    if (confirm('⚠️ آیا از حذف کامل این پروژه مطمئن هستید؟\nاین عملیات غیرقابل بازگشت است.')) {
        window.location.href = '<?= stat_url('controller=project&action=delete&id=') ?>' + id;
    }
}
</script>