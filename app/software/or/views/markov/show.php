<?php
$project = $project ?? [];
$result = $result ?? [];
$states = $states ?? [];
$ok = ($result['status'] ?? '') === 'ok';
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-project-diagram text-info me-2"></i><?= or_e($project['name']) ?></h3>
            <div class="d-flex flex-wrap gap-1">
                <span class="badge bg-<?= $ok ? 'success' : 'danger' ?>"><?= $ok ? 'موفق' : 'خطا' ?></span>
                <small class="text-muted align-self-center"><?= or_e($project['updated_at']) ?></small>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="if(confirm('حذف شود؟')) location.href='<?= or_url('controller=markov&action=delete&id=' . (int)$project['id']) ?>'">
                <i class="fas fa-trash me-1"></i><span class="d-none d-md-inline">حذف</span>
            </button>
            <a href="<?= or_url('controller=markov') ?>" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
            </a>
        </div>
    </div>

    <?php if (!$ok): ?>
        <div class="alert alert-danger"><?= or_e($result['message'] ?? 'خطا در محاسبه') ?></div>
    <?php else: ?>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body p-3 p-md-4">
                <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-cog me-2"></i>پارامترهای مدل</h6>
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered text-center small mb-0">
                                <thead class="table-light"><tr><th>از \ به</th><?php foreach ($states as $s) echo "<th>" . or_e($s) . "</th>"; ?></tr></thead>
                                <tbody>
                                    <?php foreach ($result['matrix'] as $i => $row): ?>
                                    <tr>
                                        <td class="fw-bold text-start"><?= or_e($states[$i]) ?></td>
                                        <?php foreach ($row as $val): ?>
                                            <td><?= number_format((float)$val, 3) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100 bg-light">
                            <h6 class="fw-bold small mb-2">تفسیر خودکار:</h6>
                            <pre class="mb-0 small" style="white-space: pre-wrap; line-height: 1.6;"><?= or_e($result['interpretation']) ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-bar me-2 text-info"></i>مقایسه احتمالات حالت‌ها</h6></div>
            <div class="card-body p-3 p-md-4">
                <div class="row g-1 g-md-2">
                    <?php foreach ($states as $i => $s): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="border rounded p-2 h-100">
                                <div class="fw-bold small mb-2 text-truncate"><?= or_e($s) ?></div>
                                <div class="row g-1 text-center small">
                                    <div class="col-4"><?= or_stat_box('اولیه', number_format((float)$result['initial'][$i] * 100, 1) . '%') ?></div>
                                    <div class="col-4"><?= or_stat_box('گام ' . (int)$project['steps'], number_format((float)$result['final_state'][$i] * 100, 1) . '%', 'text-info') ?></div>
                                    <div class="col-4"><?= or_stat_box('پایدار', number_format((float)$result['steady_state'][$i] * 100, 1) . '%', 'text-success') ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-2"><h6 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>روند تحول احتمالات در طول زمان</h6></div>
            <div class="card-body p-3">
                <canvas id="showMarkovChart" style="width:100%;height:300px;display:block;"></canvas>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php if ($ok): ?>
<script>
const R = <?= json_encode($result) ?>;
function drawShowChart() {
    const canvas = document.getElementById('showMarkovChart');
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 300;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const n = R.states.length;
    const stepsToShow = Math.min(R.steps, 15);
    const pL = 50, pR = 10, pT = 10, pB = 30;
    const colors = ['#0d6efd', '#198754', '#dc3545', '#ffc107', '#6f42c1', '#fd7e14', '#20c997', '#0dcaf0'];

    ctx.strokeStyle = '#dee2e6'; ctx.beginPath();
    ctx.moveTo(pL, pT); ctx.lineTo(pL, H - pB); ctx.lineTo(W - pR, H - pB); ctx.stroke();

    ctx.fillStyle = '#6c757d'; ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
    for (let i = 0; i <= 4; i++) {
        const y = H - pB - (i / 4) * (H - pT - pB);
        ctx.fillText((i * 25) + '%', pL - 4, y + 3);
    }

    const xStep = (W - pL - pR) / stepsToShow;
    for (let s = 0; s < n; s++) {
        ctx.beginPath(); ctx.strokeStyle = colors[s % colors.length]; ctx.lineWidth = 2;
        for (let step = 0; step <= stepsToShow; step++) {
            const prob = R.history[step] ? R.history[step][s] : R.steady_state[s];
            const x = pL + step * xStep;
            const y = H - pB - (prob * (H - pT - pB));
            step === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        }
        ctx.stroke();
    }
    
    let ly = 20;
    for (let s = 0; s < n; s++) {
        ctx.fillStyle = colors[s % colors.length]; ctx.fillRect(W - 130, ly, 12, 12);
        ctx.fillStyle = '#333'; ctx.textAlign = 'left'; ctx.font = '10px sans-serif';
        ctx.fillText(R.states[s].substring(0, 15), W - 114, ly + 10);
        ly += 18;
    }
}
document.addEventListener('DOMContentLoaded', drawShowChart);
window.addEventListener('resize', drawShowChart);
</script>
<?php endif; ?>