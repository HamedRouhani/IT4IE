<?php
$project = $project ?? [];
$result = $result ?? [];
$sensitivity = $sensitivity ?? null;
$tab = $tab ?? 'result';
$modelName = $modelName ?? '';
$ok = ($result['status'] ?? '') === 'ok';
?>
<div class="container-fluid py-3 py-md-4">

    <!-- هدر -->
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-people-line text-info me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-info"><?= or_e($modelName) ?></span>
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'حل‌شده' : 'خطا' ?></span>
                <small class="text-muted align-self-center">آخرین به‌روزرسانی: <?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= or_url('controller=queueing&action=edit&id=' . (int)$project['id']) ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-edit me-1"></i><span class="d-none d-md-inline">ویرایش</span>
            </a>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('حذف شود؟')) location.href='<?= or_url('controller=queueing&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=queueing') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
            </a>
        </div>
    </div>

    <!-- تب‌ها -->
    <ul class="nav nav-tabs or-tabs mb-3 mb-md-4" role="tablist">
        <li class="nav-item"><a class="nav-link <?= $tab === 'result' ? 'active' : '' ?>" href="<?= or_url('controller=queueing&action=show&id=' . (int)$project['id'] . '&tab=result') ?>"><i class="fas fa-poll me-1"></i>نتایج</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'charts' ? 'active' : '' ?>" href="<?= or_url('controller=queueing&action=show&id=' . (int)$project['id'] . '&tab=charts') ?>"><i class="fas fa-chart-bar me-1"></i>نمودارها</a></li>
        <li class="nav-item"><a class="nav-link <?= $tab === 'info' ? 'active' : '' ?>" href="<?= or_url('controller=queueing&action=show&id=' . (int)$project['id'] . '&tab=info') ?>"><i class="fas fa-info-circle me-1"></i>پارامترها</a></li>
    </ul>

    <?php if (!$ok): ?>
        <div class="alert alert-danger"><?= or_e($result['message'] ?? 'نتیجه‌ای برای این پروژه ذخیره نشده است.') ?></div>
    <?php endif; ?>

    <!-- ═══ تب نتایج ═══ -->
    <?php if ($tab === 'result' && $ok): ?>
        <div class="row g-1 g-md-2 mb-3">
            <div class="col-6 col-md-3"><?= or_stat_box('ρ بهره‌وری', number_format($result['rho'] * 100, 1) . '%', $result['rho'] > 0.85 ? 'text-danger' : 'text-success') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('P₀ خالی', number_format($result['P0'] * 100, 2) . '%', 'text-info') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('Pw مشغول', number_format($result['Pw'] * 100, 1) . '%', 'text-warning') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('L سیستم', number_format($result['L'], 3), 'text-primary') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('Lq صف', number_format($result['Lq'], 3), 'text-danger') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('W زمان سیستم', number_format($result['W'], 3), 'text-success') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('Wq زمان صف', number_format($result['Wq'], 3), 'text-warning') ?></div>
            <div class="col-6 col-md-3"><?= or_stat_box('λ / μ', number_format($result['lambda'], 2) . ' / ' . number_format($result['mu'], 2)) ?></div>
        </div>

        <?php if (!empty($result['extra'])): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="fw-bold small mb-2"><i class="fas fa-info-circle me-1 text-info"></i>جزئیات مدل</div>
                    <div class="row g-1 g-md-2">
                        <?php foreach ($result['extra'] as $k => $v): ?>
                            <div class="col-12 col-md-4">
                                <?= or_stat_box(
                                    (string)$k,
                                    is_array($v) ? implode('، ', $v) : (string)$v,
                                    'small fw-normal text-start'
                                ) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-lightbulb me-2 text-warning"></i>تفسیر تحلیلی</h6></div>
            <div class="card-body p-3">
                <pre class="mb-0 small" style="white-space: pre-wrap;"><?= or_e($result['interpretation'] ?? '') ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- ═══ تب نمودارها ═══ -->
    <?php if ($tab === 'charts' && $ok): ?>
        <?php $hasPn = !empty($result['Pn']); ?>

        <?php if ($hasPn): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>توزیع احتمال P(n)</h6></div>
            <div class="card-body p-3">
                <canvas id="pnChart" style="width:100%;height:260px;display:block;"></canvas>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-info small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            توزیع احتمال حالت ماندگار P(n) فقط برای مدل‌های مارکوفی (M/M/1، M/M/c و نسخه‌های با ظرفیت محدود) محاسبه می‌شود. برای این مدل، شاخص‌های عملکرد در تب «نتایج» را ببینید.
        </div>
        <?php endif; ?>

        <?php if (!empty($sensitivity) && count($sensitivity) > 1): ?>
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>تحلیل حساسیت نسبت به ρ</h6></div>
            <div class="card-body p-3">
                <canvas id="sensChart" style="width:100%;height:260px;display:block;"></canvas>
            </div>
        </div>
        <?php endif; ?>

<script>
const PN = <?= json_encode(array_values($result['Pn'] ?? [])) ?>;
const SENS = <?= json_encode($sensitivity ?? []) ?>;

function drawPnChart(vals) {
    const canvas = document.getElementById('pnChart');
    if (!canvas || !vals || !vals.length) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 260;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const entries = vals.map((p, n) => [n, p]).slice(0, 40);
    const maxP = Math.max(...entries.map(([, v]) => v)) * 1.15 || 1;
    const pL = 44, pR = 10, pT = 10, pB = 30;
    const bw = (W - pL - pR) / entries.length * 0.75;

    ctx.strokeStyle = '#dee2e6'; ctx.beginPath();
    ctx.moveTo(pL, pT); ctx.lineTo(pL, H - pB); ctx.lineTo(W - pR, H - pB); ctx.stroke();

    ctx.fillStyle = '#6c757d'; ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
    for (let i = 0; i <= 4; i++) {
        const y = H - pB - (i / 4) * (H - pT - pB);
        ctx.fillText((maxP * i / 4).toFixed(2), pL - 4, y + 3);
    }

    entries.forEach(([n, p], i) => {
        const x = pL + (W - pL - pR) * i / entries.length + bw * 0.125;
        const h = (p / maxP) * (H - pT - pB);
        ctx.fillStyle = '#0dcaf0';
        ctx.fillRect(x, H - pB - h, bw, h);
        if (i % Math.max(1, Math.ceil(entries.length / 12)) === 0) {
            ctx.fillStyle = '#6c757d'; ctx.textAlign = 'center';
            ctx.fillText('n=' + n, x + bw / 2, H - pB + 14);
        }
    });
}

function drawSensChart(pts) {
    const canvas = document.getElementById('sensChart');
    if (!canvas || !pts || pts.length < 2) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 260;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const pL = 44, pR = 10, pT = 10, pB = 30;
    const maxL = Math.max(...pts.map(p => p.L)) * 1.15 || 1;
    const r0 = pts[0].rho, r1 = pts[pts.length - 1].rho;

    ctx.strokeStyle = '#dee2e6'; ctx.beginPath();
    ctx.moveTo(pL, pT); ctx.lineTo(pL, H - pB); ctx.lineTo(W - pR, H - pB); ctx.stroke();

    ctx.fillStyle = '#6c757d'; ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
    for (let i = 0; i <= 4; i++) {
        const y = H - pB - (i / 4) * (H - pT - pB);
        ctx.fillText((maxL * i / 4).toFixed(1), pL - 4, y + 3);
    }
    ctx.textAlign = 'center';
    for (let i = 0; i <= 5; i++) {
        const x = pL + (W - pL - pR) * i / 5;
        ctx.fillText((r0 + (r1 - r0) * i / 5).toFixed(2), x, H - pB + 14);
    }

    const px = rho => pL + (W - pL - pR) * (rho - r0) / ((r1 - r0) || 1);
    ctx.beginPath();
    pts.forEach((p, i) => { const y = H - pB - (p.L / maxL) * (H - pT - pB); i ? ctx.lineTo(px(p.rho), y) : ctx.moveTo(px(p.rho), y); });
    ctx.strokeStyle = '#0dcaf0'; ctx.lineWidth = 2; ctx.stroke();

    ctx.beginPath();
    pts.forEach((p, i) => { const y = H - pB - (p.Lq / maxL) * (H - pT - pB); i ? ctx.lineTo(px(p.rho), y) : ctx.moveTo(px(p.rho), y); });
    ctx.strokeStyle = '#dc3545'; ctx.lineWidth = 2; ctx.setLineDash([4, 4]); ctx.stroke(); ctx.setLineDash([]);

    ctx.fillStyle = '#0dcaf0'; ctx.fillRect(W - 110, 20, 14, 10);
    ctx.fillStyle = '#333'; ctx.textAlign = 'left'; ctx.fillText('L (سیستم)', W - 92, 28);
    ctx.fillStyle = '#dc3545'; ctx.fillRect(W - 110, 35, 14, 10);
    ctx.fillText('Lq (صف)', W - 92, 43);
}

function drawAllCharts() {
    if (document.getElementById('pnChart')) drawPnChart(PN);
    if (document.getElementById('sensChart')) drawSensChart(SENS);
}
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', drawAllCharts);
else drawAllCharts();
window.addEventListener('resize', drawAllCharts);
</script>
<?php endif; ?>

    <!-- ═══ تب پارامترها ═══ -->
    <?php if ($tab === 'info'): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-cog me-2"></i>پارامترهای مدل</h6>
                            <table class="table table-borderless align-middle mb-0 small">
                                <tr>
                                    <td class="fw-bold" style="width:40%;">مدل صف</td>
                                    <td>
                                        <span class="badge bg-info"><?= or_e($modelName) ?></span>
                                        <span class="text-muted">(<?= or_e($project['model_code']) ?>)</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">λ نرخ ورود</td>
                                    <td class="font-monospace"><?= number_format((float)$project['lambda'], 4) ?> مشتری/واحد زمان</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">μ نرخ خدمت</td>
                                    <td class="font-monospace"><?= number_format((float)$project['mu'], 4) ?> مشتری/واحد زمان</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">تعداد سرور c</td>
                                    <td class="font-monospace"><?= (int)$project['servers'] ?></td>
                                </tr>
                                <?php if (in_array($project['model_code'], ['MM1K', 'MMcK'])): ?>
                                <tr>
                                    <td class="fw-bold">ظرفیت K</td>
                                    <td class="font-monospace"><?= $project['capacity'] !== null ? (int)$project['capacity'] : 'نامحدود' ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if ($project['model_code'] === 'MG1'): ?>
                                <tr>
                                    <td class="fw-bold">σ انحراف زمان خدمت</td>
                                    <td class="font-monospace"><?= $project['service_std'] !== null ? number_format((float)$project['service_std'], 4) : '-' ?></td>
                                </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="fw-bold mb-3 text-success"><i class="fas fa-clock me-2"></i>اطلاعات پروژه</h6>
                            <table class="table table-borderless align-middle mb-0 small">
                                <tr>
                                    <td class="fw-bold" style="width:40%;">شناسه پروژه</td>
                                    <td class="font-monospace">#<?= (int)$project['id'] ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">وضعیت</td>
                                    <td>
                                        <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>">
                                            <?= $ok ? 'حل‌شده' : 'خطا' ?>
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">تاریخ ایجاد</td>
                                    <td><?= or_e($project['created_at']) ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">آخرین به‌روزرسانی</td>
                                    <td><?= or_e($project['updated_at']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <?php if (!empty($project['description'])): ?>
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <h6 class="fw-bold mb-2 text-muted"><i class="fas fa-align-right me-2"></i>توضیحات پروژه</h6>
                            <p class="small mb-0" style="line-height:1.8;"><?= nl2br(or_e($project['description'])) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="mt-4 text-center">
                    <a href="<?= or_url('controller=queueing&action=edit&id=' . (int)$project['id']) ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i> ویرایش پارامترها
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>