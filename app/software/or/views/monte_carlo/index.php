<?php
/**
 * OR Analyzer - شبیه‌سازی مونت‌کارلو
 * مسیر: app/software/or/views/monte_carlo/index.php
 */

// تعریف تابع کمکی PHP برای بخش "پروژه‌های قبلی"
if (!function_exists('or_stat_box')) {
    function or_stat_box(string $label, string $value, string $valueClass = ''): string {
        $esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
        return '<div class="border rounded bg-light text-center p-2 h-100">'
            . '<small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">' . $esc($label) . '</small>'
            . '<strong class="d-block' . ($valueClass !== '' ? ' ' . $valueClass : '') . '" style="line-height:1.3;word-break:break-word;">' . $esc($value) . '</strong>'
            . '</div>';
    }
}

$distributions = $distributions ?? [];
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-dice text-warning me-2"></i>شبیه‌سازی مونت‌کارلو</h3>
            <small class="text-muted">مدل‌سازی عدم قطعیت و تحلیل ریسک با روش مونت‌کارلو</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- فرم -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تنظیمات شبیه‌سازی</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">متغیرهای تصادفی</label>
                        <div id="variablesBox"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addVariable()">
                            <i class="fas fa-plus me-1"></i> افزودن متغیر
                        </button>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">عبارت تابع هدف</label>
                        <input type="text" id="funcExpr" class="form-control form-control-sm font-monospace" placeholder="مثال: x1 * x2 + x3" value="x1 * x2">
                        <small class="text-muted">از عملگرهای +, -, *, /, ^ و پرانتز استفاده کنید</small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">تعداد تکرار</label>
                            <input type="number" id="iterations" class="form-control form-control-sm" value="10000" min="100" max="1000000">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">Seed (اختیاری)</label>
                            <input type="number" id="seed" class="form-control form-control-sm" placeholder="برای تکرارپذیری">
                        </div>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه">
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-warning text-dark fw-bold" onclick="runSimulation()">
                            <i class="fas fa-play me-1"></i> اجرای شبیه‌سازی
                        </button>
                    </div>

                    <div class="mt-3 small">
                        <div class="fw-bold mb-1">نمونه‌های سریع:</div>
                        <div class="d-flex flex-wrap gap-1">
                            <button class="btn btn-sm btn-outline-warning py-0" onclick="loadPreset('profit')">سود</button>
                            <button class="btn btn-sm btn-outline-warning py-0" onclick="loadPreset('cost')">هزینه</button>
                            <button class="btn btn-sm btn-outline-warning py-0" onclick="loadPreset('inventory')">موجودی</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نتایج -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>نتایج شبیه‌سازی</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-dice fa-3x mb-3"></i>
                        <p class="small mb-0">متغیرها و تابع را تعریف کنید، سپس «اجرای شبیه‌سازی» را بزنید.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- پروژه‌های قبلی -->
    <?php if (!empty($projects)): ?>
    <div class="card border-0 shadow-sm mt-3 mt-md-4">
        <div class="card-header bg-white py-2 py-md-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>پروژه‌های قبلی</h6>
            <span class="badge bg-secondary"><?= count($projects) ?> مورد</span>
        </div>
        <div class="card-body p-2 p-md-3">
            <div class="row g-2 g-md-3">
                <?php foreach ($projects as $p):
                    $r = json_decode($p['result_json'] ?? '{}', true) ?: [];
                    $stats = $r['stats'] ?? [];
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <div class="row g-1 mb-2">
                                    <div class="col-6"><?= or_stat_box('تکرار', number_format((int)$p['iterations'], 0)) ?></div>
                                    <div class="col-6"><?= or_stat_box('میانگین', number_format((float)($stats['mean'] ?? 0), 2)) ?></div>
                                </div>
                                <small class="text-muted d-block"><i class="fas fa-clock me-1"></i><?= htmlspecialchars($p['updated_at']) ?></small>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <div class="d-flex gap-1">
                                    <a href="<?= or_url('controller=monte_carlo&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill">
                                        <i class="fas fa-eye me-1"></i>مشاهده
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProject(<?= (int)$p['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
const DISTRIBUTIONS = <?= json_encode($distributions, JSON_UNESCAPED_UNICODE) ?>;
let varCounter = 0;

// ✅ تابع کمکی جاوااسکریپت برای ساخت جعبه‌های آماری (جایگزین تابع PHP در JS)
function or_stat_box(label, value, valueClass = '') {
    return `<div class="border rounded bg-light text-center p-2 h-100">
        <small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">${label}</small>
        <strong class="d-block ${valueClass}" style="line-height:1.3;word-break:break-word;">${value}</strong>
    </div>`;
}

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = String(s ?? '');
    return d.innerHTML;
}

function addVariable(name = '', dist = 'normal', params = {}) {
    varCounter++;
    const id = 'var_' + varCounter;
    const html = `
        <div class="border rounded p-2 mb-2" id="${id}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <input type="text" class="form-control form-control-sm var-name" placeholder="نام متغیر (مثلاً x1)" value="${name || 'x' + varCounter}" style="max-width:120px;">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('${id}').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <select class="form-select form-select-sm var-dist mb-2" onchange="renderParams('${id}')">
                ${Object.entries(DISTRIBUTIONS).map(([k, v]) => `<option value="${k}" ${k === dist ? 'selected' : ''}>${v.name}</option>`).join('')}
            </select>
            <div class="var-params"></div>
        </div>
    `;
    document.getElementById('variablesBox').insertAdjacentHTML('beforeend', html);
    renderParams(id, dist, params);
}

function renderParams(id, dist, params = {}) {
    const distVal = dist || document.querySelector(`#${id} .var-dist`).value;
    const paramDefs = DISTRIBUTIONS[distVal].params;
    const html = paramDefs.map(p => `
        <div class="mb-1">
            <label class="form-label small mb-0">${p}</label>
            <input type="number" step="any" class="form-control form-control-sm var-param" data-param="${p}" value="${params[p] ?? 0}">
        </div>
    `).join('');
    document.querySelector(`#${id} .var-params`).innerHTML = html;
}

function getVariables() {
    const vars = [];
    document.querySelectorAll('#variablesBox > div').forEach(div => {
        const name = div.querySelector('.var-name').value.trim();
        const dist = div.querySelector('.var-dist').value;
        const params = {};
        div.querySelectorAll('.var-param').forEach(inp => {
            params[inp.dataset.param] = parseFloat(inp.value) || 0;
        });
        if (name) vars.push({ name, dist, params });
    });
    return vars;
}

function loadPreset(type) {
    document.getElementById('variablesBox').innerHTML = '';
    varCounter = 0;
    if (type === 'profit') {
        addVariable('price', 'normal', { mean: 100, std: 10 });
        addVariable('cost', 'triangular', { min: 50, mode: 60, max: 80 });
        addVariable('demand', 'normal', { mean: 500, std: 50 });
        document.getElementById('funcExpr').value = '(price - cost) * demand';
    } else if (type === 'cost') {
        addVariable('material', 'normal', { mean: 200, std: 20 });
        addVariable('labor', 'uniform', { min: 100, max: 150 });
        addVariable('overhead', 'normal', { mean: 50, std: 5 });
        document.getElementById('funcExpr').value = 'material + labor + overhead';
    } else if (type === 'inventory') {
        addVariable('demand', 'poisson', { lambda: 100 });
        addVariable('lead_time', 'exponential', { lambda: 0.5 });
        document.getElementById('funcExpr').value = 'demand * lead_time';
    }
}

async function runSimulation() {
    const variables = getVariables();
    const funcExpr = document.getElementById('funcExpr').value.trim();
    const iterations = parseInt(document.getElementById('iterations').value) || 10000;
    const seed = document.getElementById('seed').value ? parseInt(document.getElementById('seed').value) : null;
    const save = document.getElementById('saveCheck').checked;
    const name = document.getElementById('projectName').value.trim();

    if (variables.length === 0) { alert('حداقل یک متغیر اضافه کنید.'); return; }
    if (!funcExpr) { alert('عبارت تابع هدف الزامی است.'); return; }

    const payload = { variables, function: funcExpr, iterations, seed, save, name };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div><p class="small mt-2">در حال شبیه‌سازی...</p></div>';

    try {
        const res = await fetch('<?= or_url('controller=monte_carlo&action=simulate') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { 
            box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; 
            return; 
        }
        renderResult(data.result);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(r) {
    const box = document.getElementById('resultBox');
    let html = `<div class="alert alert-warning py-2 small mb-3">
        <strong>تابع:</strong> <span class="font-monospace">${escapeHtml(r.function)}</span> | 
        <strong>تکرار:</strong> ${r.iterations.toLocaleString()} | 
        <strong>زمان:</strong> ${r.elapsed_ms} ms
    </div>`;

    html += `<div class="row g-1 g-md-2 mb-3">
        <div class="col-6 col-md-3">${or_stat_box('میانگین', r.stats.mean.toFixed(4), 'text-primary')}</div>
        <div class="col-6 col-md-3">${or_stat_box('انحراف معیار', r.stats.std.toFixed(4), 'text-danger')}</div>
        <div class="col-6 col-md-3">${or_stat_box('میانه', r.stats.median.toFixed(4), 'text-info')}</div>
        <div class="col-6 col-md-3">${or_stat_box('واریانس', r.stats.variance.toFixed(4))}</div>
        <div class="col-6 col-md-3">${or_stat_box('حداقل', r.stats.min.toFixed(4))}</div>
        <div class="col-6 col-md-3">${or_stat_box('حداکثر', r.stats.max.toFixed(4))}</div>
        <div class="col-6 col-md-3">${or_stat_box('CI 95% ↓', r.stats.ci95_lower.toFixed(4), 'text-success')}</div>
        <div class="col-6 col-md-3">${or_stat_box('CI 95% ↑', r.stats.ci95_upper.toFixed(4), 'text-success')}</div>
    </div>`;

    html += `<div class="row g-1 g-md-2 mb-3">
        <div class="col-6 col-md-3">${or_stat_box('P(منفی)', r.risk.p_negative + '%', r.risk.p_negative > 10 ? 'text-danger' : '')}</div>
        <div class="col-6 col-md-3">${or_stat_box('P(مثبت)', r.risk.p_positive + '%', 'text-success')}</div>
        <div class="col-6 col-md-3">${or_stat_box('چولگی', r.stats.skewness.toFixed(3))}</div>
        <div class="col-6 col-md-3">${or_stat_box('کشیدگی', r.stats.kurtosis.toFixed(3))}</div>
    </div>`;

    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-chart-bar me-1"></i>هیستوگرام خروجی</h6>
             <canvas id="histChart" style="width:100%;height:260px;display:block;" class="mb-3"></canvas>`;

    html += `<div class="alert alert-light border py-2 small mb-0">
        <pre class="mb-0" style="white-space: pre-wrap;">${escapeHtml(r.interpretation)}</pre></div>`;

    box.innerHTML = html;
    drawHistogram(r.histogram);
}

function drawHistogram(hist) {
    const canvas = document.getElementById('histChart');
    if (!canvas || !hist) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 260;
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

async function deleteProject(id) {
    if (!confirm('آیا از حذف این پروژه مطمئن هستید؟')) return;
    try {
        const res = await fetch('<?= or_url('controller=monte_carlo&action=delete&id=') ?>' + id);
        if (res.ok) location.reload();
        else alert('خطا در حذف پروژه');
    } catch (e) { 
        alert('خطای شبکه: ' + e.message); 
    }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});

document.addEventListener('DOMContentLoaded', () => {
    loadPreset('profit');
});
</script>