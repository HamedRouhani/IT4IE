<?php
/**
 * OR Analyzer - نمایش جزئیات پروژه مونت‌کارلو
 * مسیر: app/software/or/views/monte_carlo/show.php
 */

// تعریف تابع کمکی در صورت عدم وجود
if (!function_exists('or_stat_box')) {
    function or_stat_box(string $label, string $value, string $valueClass = ''): string {
        $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        return '<div class="border rounded bg-light text-center p-2 h-100">'
            . '<small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">' . $esc($label) . '</small>'
            . '<strong class="d-block' . ($valueClass !== '' ? ' ' . $valueClass : '') . '" style="line-height:1.3;word-break:break-word;">' . $esc($value) . '</strong>'
            . '</div>';
    }
}

$project = $project ?? [];
$result  = $result ?? [];
$variables = $variables ?? [];
$ok = ($result['status'] ?? '') === 'ok';
?>
<div class="container-fluid py-3 py-md-4">

    <!-- هدر صفحه -->
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-dice text-warning me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'موفق' : 'خطا' ?></span>
                <small class="text-muted align-self-center">آخرین به‌روزرسانی: <?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= or_url('controller=monte_carlo&action=show&id=' . (int)$project['id']) ?>" class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i><span class="d-none d-md-inline">به‌روزرسانی</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('آیا از حذف این پروژه مطمئن هستید؟')) location.href='<?= or_url('controller=monte_carlo&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=monte_carlo') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت به لیست</span>
            </a>
        </div>
    </div>

    <?php if (!$ok): ?>
        <div class="alert alert-danger">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>خطا در شبیه‌سازی</h6>
            <p class="mb-0 small"><?= or_e($result['message'] ?? 'نتیجه معتبری برای این پروژه ذخیره نشده است.') ?></p>
        </div>
    <?php else: ?>

        <!-- اطلاعات کلی -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold mb-2 text-primary"><i class="fas fa-cog me-2"></i>تنظیمات مدل</h6>
                        <table class="table table-borderless align-middle mb-0 small">
                            <tr>
                                <td class="fw-bold" style="width:40%;">عبارت تابع هدف</td>
                                <td class="font-monospace bg-light px-2 py-1 rounded"><?= or_e($result['function']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">تعداد تکرار</td>
                                <td><?= number_format((int)$result['iterations']) ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">زمان اجرا</td>
                                <td><?= number_format((float)$result['elapsed_ms'], 2) ?> میلی‌ثانیه</td>
                            </tr>
                            <?php if (!empty($project['seed'])): ?>
                            <tr>
                                <td class="fw-bold">Seed (تکرارپذیری)</td>
                                <td class="font-monospace"><?= (int)$project['seed'] ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="fw-bold mb-2 text-success"><i class="fas fa-random me-2"></i>متغیرهای تصادفی</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0 small">
                                <thead class="table-light">
                                    <tr><th>نام</th><th>توزیع</th><th>پارامترها</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($variables as $v): 
                                        $paramsStr = [];
                                        foreach ($v['params'] as $k => $val) $paramsStr[] = "$k=$val";
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?= or_e($v['name']) ?></td>
                                        <td><?= or_e($v['dist']) ?></td>
                                        <td class="font-monospace small"><?= implode(', ', $paramsStr) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نتایج آماری -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-warning"></i>آماره‌های توصیفی خروجی</h6></div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-1 g-md-2 mb-3">
                    <div class="col-6 col-md-3"><?= or_stat_box('میانگین', number_format((float)$result['stats']['mean'], 4), 'text-primary') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('میانه', number_format((float)$result['stats']['median'], 4), 'text-info') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('انحراف معیار', number_format((float)$result['stats']['std'], 4), 'text-danger') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('واریانس', number_format((float)$result['stats']['variance'], 4)) ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('حداقل', number_format((float)$result['stats']['min'], 4)) ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('حداکثر', number_format((float)$result['stats']['max'], 4)) ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('CI 95% ↓', number_format((float)$result['stats']['ci95_lower'], 4), 'text-success') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('CI 95% ↑', number_format((float)$result['stats']['ci95_upper'], 4), 'text-success') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('چولگی', number_format((float)$result['stats']['skewness'], 3)) ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('کشیدگی', number_format((float)$result['stats']['kurtosis'], 3)) ?></div>
                </div>
            </div>
        </div>

        <!-- تحلیل ریسک -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-shield-alt me-2 text-danger"></i>تحلیل ریسک (احتمالات)</h6></div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-1 g-md-2">
                    <div class="col-6 col-md-3"><?= or_stat_box('P(خروجی منفی)', number_format((float)$result['risk']['p_negative'], 2) . '%', (float)$result['risk']['p_negative'] > 10 ? 'text-danger fw-bold' : '') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('P(خروجی صفر)', number_format((float)$result['risk']['p_zero'], 2) . '%') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('P(خروجی مثبت)', number_format((float)$result['risk']['p_positive'], 2) . '%', 'text-success') ?></div>
                    <div class="col-6 col-md-3"><?= or_stat_box('P(زیر میانگین)', number_format((float)$result['risk']['p_below_mean'], 2) . '%') ?></div>
                </div>
            </div>
        </div>

        <!-- نمودار هیستوگرام -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-area me-2 text-info"></i>توزیع فراوانی خروجی (هیستوگرام)</h6></div>
            <div class="card-body p-3">
                <canvas id="histChart" style="width:100%;height:280px;display:block;"></canvas>
            </div>
        </div>

        <!-- تفسیر -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>تفسیر خودکار</h6></div>
            <div class="card-body p-3 p-md-4">
                <pre class="mb-0 small" style="white-space: pre-wrap; line-height: 1.7;"><?= or_e($result['interpretation']) ?></pre>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php if ($ok && !empty($result['histogram'])): ?>
<script>
const HIST = <?= json_encode($result['histogram']) ?>;

function drawHistogram(hist) {
    const canvas = document.getElementById('histChart');
    if (!canvas || !hist) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 280;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const maxCount = Math.max(...hist.counts) * 1.15;
    const pL = 44, pR = 10, pT = 10, pB = 30;
    const bw = (W - pL - pR) / hist.bins * 0.85;

    ctx.strokeStyle = '#dee2e6'; ctx.beginPath();
    ctx.moveTo(pL, pT); ctx.lineTo(pL, H - pB); ctx.lineTo(W - pR, H - pB); ctx.stroke();

    ctx.fillStyle = '#6c757d'; ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
    for (let i = 0; i <= 4; i++) {
        const y = H - pB - (i / 4) * (H - pT - pB);
        ctx.fillText(Math.round(maxCount * i / 4), pL - 4, y + 3);
    }

    hist.counts.forEach((c, i) => {
        const x = pL + (W - pL - pR) * i / hist.bins + bw * 0.075;
        const h = (c / maxCount) * (H - pT - pB);
        ctx.fillStyle = '#ffc107';
        ctx.fillRect(x, H - pB - h, bw, h);
        if (i % Math.max(1, Math.ceil(hist.bins / 10)) === 0) {
            ctx.fillStyle = '#6c757d'; ctx.textAlign = 'center';
            ctx.fillText(hist.labels[i].toFixed(1), x + bw / 2, H - pB + 14);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => drawHistogram(HIST));
window.addEventListener('resize', () => drawHistogram(HIST));
</script>
<?php endif; ?>