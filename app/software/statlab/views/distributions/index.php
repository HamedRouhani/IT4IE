<?php
/**
 * StatLab - ماشین‌حساب توزیع‌های احتمال (ریسپانسیو)
 */
?>
<div class="container-fluid py-3 py-md-4">
    <div class="statlab-page-header">
        <h3 class="mb-0"><i class="fas fa-dice text-success me-2"></i>توزیع‌های احتمال</h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- فرم ورودی -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تنظیمات توزیع</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">نوع توزیع</label>
                        <select id="distSelect" class="form-select form-select-sm" onchange="renderParams()">
                            <?php foreach ($distributions as $code => $m): ?>
                                <option value="<?= $code ?>"><?= stat_e($m['name_fa']) ?>
                                    (<?= $m['family'] === 'discrete' ? 'گسسته' : 'پیوسته' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="paramsContainer" class="row g-2 mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">نوع محاسبه</label>
                        <select id="calcMode" class="form-select form-select-sm" onchange="onModeChange()">
                            <option value="left">دم چپ: P(X ≤ x)</option>
                            <option value="right">دم راست: P(X ≥ x)</option>
                            <option value="between">بازه: P(x₁ ≤ X ≤ x₂)</option>
                            <option value="quantile">معکوس: یافتن x به ازای P</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-4" id="boxX">
                            <label class="form-label small mb-1">x</label>
                            <input type="number" step="any" id="inputX" class="form-control form-control-sm" value="1.96">
                        </div>
                        <div class="col-4 d-none" id="boxX2">
                            <label class="form-label small mb-1">x₂</label>
                            <input type="number" step="any" id="inputX2" class="form-control form-control-sm" value="3">
                        </div>
                        <div class="col-4 d-none" id="boxP">
                            <label class="form-label small mb-1">احتمال P</label>
                            <input type="number" step="any" min="0.001" max="0.999" id="inputP" class="form-control form-control-sm" value="0.95">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-success" onclick="calculate()">
                            <i class="fas fa-calculator me-1"></i> محاسبه
                        </button>
                    </div>
                </div>
            </div>

            <!-- کارت خلاصه -->
            <div class="card border-0 shadow-sm mt-3" id="summaryCard" style="display:none;">
                <div class="card-body p-3">
                    <h6 class="fw-bold small mb-2"><i class="fas fa-info-circle me-1 text-info"></i>ویژگی‌های توزیع</h6>
                    <div class="row g-2" id="summaryBoxes"></div>
                </div>
            </div>
        </div>

        <!-- نتایج و نمودار -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 py-md-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-chart-area me-2"></i>نتیجه و نمودار</h6>
                    <span id="resultBadge" class="badge bg-light text-dark d-none"></span>
                </div>
                <div class="card-body p-3">
                    <div id="resultText" class="alert alert-info py-2 small mb-3">
                        توزیع و پارامترها را انتخاب و روی «محاسبه» کلیک کنید.
                    </div>
                    <div class="statlab-scroll-y" style="max-height: 420px;">
                        <canvas id="distChart" width="800" height="420"
                                style="width:100%; height:auto; min-width:280px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const DIST_META = <?= json_encode($distributions, JSON_UNESCAPED_UNICODE) ?>;
let lastChart = null;

// ─── ساخت ورودی‌های پارامتر بر اساس توزیع ───
function renderParams() {
    const code = document.getElementById('distSelect').value;
    const meta = DIST_META[code];
    const box = document.getElementById('paramsContainer');
    box.innerHTML = meta.params.map(p => `
        <div class="col-6">
            <label class="form-label small mb-1">${p.label}</label>
            <input type="number" step="${p.step}" class="form-control form-control-sm param-input"
                   data-key="${p.key}" value="${p.default}">
        </div>`).join('');
}

function onModeChange() {
    const mode = document.getElementById('calcMode').value;
    document.getElementById('boxX').classList.toggle('d-none', mode === 'quantile');
    document.getElementById('boxX2').classList.toggle('d-none', mode !== 'between');
    document.getElementById('boxP').classList.toggle('d-none', mode !== 'quantile');
}

function getParams() {
    const params = {};
    document.querySelectorAll('.param-input').forEach(i => {
        params[i.dataset.key] = parseFloat(i.value) || 0;
    });
    return params;
}

// ─── محاسبه ───
async function calculate() {
    const payload = {
        dist: document.getElementById('distSelect').value,
        params: getParams(),
        mode: document.getElementById('calcMode').value,
        x:  parseFloat(document.getElementById('inputX').value) || 0,
        x2: parseFloat(document.getElementById('inputX2').value) || 0,
        p:  parseFloat(document.getElementById('inputP').value) || 0.95,
    };

    document.getElementById('resultText').innerHTML =
        '<i class="fas fa-spinner fa-spin me-1"></i> در حال محاسبه...';

    try {
        const res = await fetch('<?= stat_url('controller=distribution&action=calculate') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (!data.success) {
            document.getElementById('resultText').innerHTML =
                '<div class="alert alert-danger py-2 small mb-0">❌ ' + data.error + '</div>';
            return;
        }

        document.getElementById('resultText').innerHTML =
            '<strong class="fs-6">✅ ' + data.result.text + '</strong>';

        const badge = document.getElementById('resultBadge');
        badge.textContent = DIST_META[payload.dist].name_fa;
        badge.classList.remove('d-none');

        // خلاصه
        const sc = document.getElementById('summaryCard');
        sc.style.display = 'block';
        document.getElementById('summaryBoxes').innerHTML =
            sumBox('میانگین', data.mean != null ? Number(data.mean).toFixed(4) : '-') +
            sumBox('واریانس', data.variance != null ? Number(data.variance).toFixed(4) : '-');

        drawChart(data);
    } catch (e) {
        document.getElementById('resultText').innerHTML =
            '<div class="alert alert-danger py-2 small mb-0">❌ خطای شبکه: ' + e.message + '</div>';
    }
}

function sumBox(label, value) {
    return `<div class="col-6"><div class="stat-box"><small>${label}</small><strong>${value}</strong></div></div>`;
}

// ─── رسم نمودار با Canvas خالص ───
function drawChart(data) {
    const canvas = document.getElementById('distChart');
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 760, H = 400;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const curve = data.curve;
    const xs = curve.map(p => p.x), ys = curve.map(p => p.y);
    const minX = Math.min(...xs), maxX = Math.max(...xs);
    const maxY = Math.max(...ys) * 1.15 || 1;

    const padL = 46, padR = 12, padT = 14, padB = 30;
    const sx = x => padL + (x - minX) / (maxX - minX || 1) * (W - padL - padR);
    const sy = y => H - padB - (y / maxY) * (H - padT - padB);

    // محور‌ها
    ctx.strokeStyle = '#dee2e6'; ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padL, padT); ctx.lineTo(padL, H - padB); ctx.lineTo(W - padR, H - padB);
    ctx.stroke();

    // برچسب محور x
    ctx.fillStyle = '#6c757d'; ctx.font = '10px Vazirmatn, sans-serif'; ctx.textAlign = 'center';
    for (let i = 0; i <= 5; i++) {
        const vx = minX + (maxX - minX) * i / 5;
        ctx.fillText(Number(vx.toFixed(2)), sx(vx), H - padB + 14);
    }

    const region = data.result.region || null;
    const rLo = region ? (region[0] != null ? region[0] : minX) : null;
    const rHi = region ? (region[1] != null ? region[1] : maxX) : null;

    if (data.family === 'discrete') {
        // میله‌ها
        const bw = Math.max(2, (W - padL - padR) / (xs.length || 1) * 0.5);
        curve.forEach(p => {
            const inR = region && p.x >= rLo - 1e-9 && p.x <= rHi + 1e-9;
            ctx.fillStyle = inR ? 'rgba(25,135,84,0.75)' : 'rgba(13,110,253,0.45)';
            ctx.fillRect(sx(p.x) - bw / 2, sy(p.y), bw, (H - padB) - sy(p.y));
        });
    } else {
        // ناحیه سایه‌دار
        if (region) {
            ctx.beginPath();
            ctx.moveTo(sx(rLo), sy(0));
            curve.filter(p => p.x >= rLo && p.x <= rHi).forEach(p => ctx.lineTo(sx(p.x), sy(p.y)));
            ctx.lineTo(sx(rHi), sy(0));
            ctx.closePath();
            ctx.fillStyle = 'rgba(25,135,84,0.25)';
            ctx.fill();
        }
        // منحنی
        ctx.beginPath();
        curve.forEach((p, i) => i ? ctx.lineTo(sx(p.x), sy(p.y)) : ctx.moveTo(sx(p.x), sy(p.y)));
        ctx.strokeStyle = '#0d6efd'; ctx.lineWidth = 2;
        ctx.stroke();
    }
    lastChart = data;
}

// رسم اولیه و ریسپانسیو
document.addEventListener('DOMContentLoaded', () => {
    renderParams();
    onModeChange();
});
window.addEventListener('resize', () => { if (lastChart) drawChart(lastChart); });
</script>