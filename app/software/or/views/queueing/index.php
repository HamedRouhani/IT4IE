<?php
/**
 * OR Analyzer - نظریه صف (Queueing Theory)
 * ✅ اصلاح‌شده: استفاده از or_url()/or_e() به‌جای stat_url()/stat_e()
 */

//Fallback ایمن در صورت لود نبودن Helpers
if (!function_exists('or_url')) {
    function or_url(string $query = ''): string {
        $base = defined('CURRENT_MODULE_URL') ? CURRENT_MODULE_URL : '/software/or-analyzer/';
        return $base . ($query !== '' ? '?' . $query : '');
    }
}
if (!function_exists('or_e')) {
    function or_e($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}

$models = $models ?? [];
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-people-line text-info me-2"></i>نظریه صف (Queueing Theory)</h3>
            <small class="text-muted">تحلیل سیستم‌های صف و ارزیابی عملکرد خدمات</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- فرم -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تنظیمات مدل صف</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">مدل صف</label>
                        <select id="modelSelect" class="form-select form-select-sm" onchange="renderModel()">
                            <?php foreach ($models as $code => $m): ?>
                                <option value="<?= or_e($code) ?>"><?= or_e($m['name']) ?> — <?= or_e($m['desc']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">λ (نرخ ورود)</label>
                            <input type="number" step="any" id="lambda" class="form-control form-control-sm" value="4" min="0.0001">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">μ (نرخ خدمت)</label>
                            <input type="number" step="any" id="mu" class="form-control form-control-sm" value="5" min="0.0001">
                        </div>
                    </div>

                    <div class="row g-2 mb-3" id="serversBox">
                        <div class="col-6">
                            <label class="form-label small mb-1">تعداد سرور c</label>
                            <input type="number" id="servers" class="form-control form-control-sm" value="2" min="1" max="50">
                        </div>
                        <div class="col-6" id="capacityBox" style="display:none;">
                            <label class="form-label small mb-1">ظرفیت K</label>
                            <input type="number" id="capacity" class="form-control form-control-sm" value="10" min="1">
                        </div>
                    </div>

                    <div class="mb-3" id="stdBox" style="display:none;">
                        <label class="form-label small mb-1">σ (انحراف زمان خدمت)</label>
                        <input type="number" step="any" id="serviceStd" class="form-control form-control-sm" value="0.1" min="0">
                    </div>

                    <hr class="my-2">

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه (اختیاری)">
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-info text-white" onclick="solveQueue()">
                            <i class="fas fa-calculator me-1"></i> حل مدل
                        </button>
                    </div>

                    <div class="mt-3 small">
                        <div class="fw-bold mb-1">نمونه‌های سریع:</div>
                        <div class="d-flex flex-wrap gap-1">
                            <button class="btn btn-sm btn-outline-info py-0" onclick="loadPreset('bank')">بانک</button>
                            <button class="btn btn-sm btn-outline-info py-0" onclick="loadPreset('call')">مرکز تماس</button>
                            <button class="btn btn-sm btn-outline-info py-0" onclick="loadPreset('market')">سوپرمارکت</button>
                            <button class="btn btn-sm btn-outline-info py-0" onclick="loadPreset('emergency')">اورژانس</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- نتایج -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>نتیجه تحلیل</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-people-line fa-3x mb-3"></i>
                        <p class="small mb-0">پارامترهای مدل را وارد و «حل مدل» را بزنید.</p>
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
                    $r  = json_decode($p['result_json'] ?? '{}', true) ?: [];
                    $ok = ($r['status'] ?? '') === 'ok';
                    $rho = (float)$p['mu'] > 0 ? (float)$p['lambda'] / ((float)$p['mu'] * max(1, (int)$p['servers'])) : 0;
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100 or-queueing-project-card">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h6 class="fw-bold mb-0 text-truncate"><?= or_e($p['name']) ?></h6>
                                    <span class="badge bg-info text-nowrap"><?= or_e($p['model_code']) ?></span>
                                </div>
                                <div class="row g-1 mb-2">
                                    <div class="col-6"><?= or_stat_box('ρ بهره‌وری', number_format($rho, 2), $rho >= 1 ? 'text-danger' : '') ?></div>
                                    <div class="col-6"><?= or_stat_box('L سیستم', $ok ? number_format((float)$r['L'], 2) : '-') ?></div>
                                    <div class="col-6"><?= or_stat_box('W زمان', $ok ? number_format((float)$r['W'], 2) : '-') ?></div>
                                    <div class="col-6"><?= or_stat_box('وضعیت', $ok ? 'حل‌شده' : 'خطا', 'small ' . ($ok ? 'text-success' : 'text-danger')) ?></div>
                                </div>
                                <small class="text-muted d-block"><i class="fas fa-clock me-1"></i><?= or_e($p['updated_at']) ?></small>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
                                <div class="d-flex gap-1">
                                    <a href="<?= or_url('controller=queueing&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill text-nowrap">
                                        <i class="fas fa-eye me-1"></i>مشاهده
                                    </a>
                                    <a href="<?= or_url('controller=queueing&action=edit&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-primary flex-fill text-nowrap">
                                        <i class="fas fa-edit me-1"></i>ویرایش
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-shrink-0" onclick="deleteProject(<?= (int)$p['id'] ?>)" title="حذف">
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
const MODELS = <?= json_encode($models, JSON_UNESCAPED_UNICODE) ?>;
const SOLVE_URL = '<?= or_url('controller=queueing&action=solve') ?>';
const DELETE_URL = '<?= or_url('controller=queueing&action=delete&id=') ?>';

function renderModel() {
    const code = document.getElementById('modelSelect').value;
    document.getElementById('serversBox').style.display = ['MMc','MMcK'].includes(code) ? '' : 'none';
    document.getElementById('capacityBox').style.display = ['MM1K','MMcK'].includes(code) ? '' : 'none';
    document.getElementById('stdBox').style.display = (code === 'MG1') ? '' : 'none';
}

function loadPreset(type) {
    const presets = {
        bank:      { model: 'MMc',  lambda: 10, mu: 4, servers: 3 },
        call:      { model: 'MMcK', lambda: 30, mu: 6, servers: 5, capacity: 20 },
        market:    { model: 'MM1',  lambda: 20, mu: 25 },
        emergency: { model: 'MM1',  lambda: 2,  mu: 3 },
    };
    const p = presets[type];
    if (!p) return;
    document.getElementById('modelSelect').value = p.model;
    document.getElementById('lambda').value = p.lambda;
    document.getElementById('mu').value = p.mu;
    if (p.servers) document.getElementById('servers').value = p.servers;
    if (p.capacity) document.getElementById('capacity').value = p.capacity;
    renderModel();
}

function escapeHtml(s){const d=document.createElement('div');d.textContent=String(s??'');return d.innerHTML;}
function sb(label, value, cls = '') {
    return `<div class="col-6 col-md-3"><div class="or-stat-box"><small>${label}</small><strong class="${cls}">${value}</strong></div></div>`;
}

async function solveQueue() {
    const payload = {
        model: document.getElementById('modelSelect').value,
        lambda: parseFloat(document.getElementById('lambda').value),
        mu: parseFloat(document.getElementById('mu').value),
        servers: parseInt(document.getElementById('servers').value) || 1,
        capacity: document.getElementById('capacity').value ? parseInt(document.getElementById('capacity').value) : null,
        service_std: parseFloat(document.getElementById('serviceStd').value) || 0,
        save: document.getElementById('saveCheck').checked,
        name: document.getElementById('projectName').value.trim(),
    };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info"></div><p class="small mt-2">در حال محاسبه...</p></div>';

    try {
        const res = await fetch(SOLVE_URL, {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; return; }
        renderResult(data);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(data) {
    const r = data.result;
    const box = document.getElementById('resultBox');

    if (r.status === 'unstable') {
        box.innerHTML = `<div class="alert alert-danger py-3">
            <h6>⚠️ ${escapeHtml(r.message)}</h6>
            <p class="small mb-0">${escapeHtml(r.suggestion)}</p>
        </div>`;
        return;
    }

    let html = `<div class="alert alert-info py-2 small mb-3">
        <strong>${escapeHtml(r.model)}</strong> — λ=${r.lambda}, μ=${r.mu}` +
        (r.servers > 1 ? `, c=${r.servers}` : '') +
        (r.capacity ? `, K=${r.capacity}` : '') + `</div>`;

    html += `<div class="row g-1 g-md-2 mb-3">` +
        sb('ρ (بهره‌وری)', (r.rho * 100).toFixed(1) + '%', r.rho > 0.85 ? 'text-danger' : 'text-success') +
        sb('P₀ (خالی)', (r.P0 * 100).toFixed(2) + '%', 'text-info') +
        sb('Pw (مشغول)', (r.Pw * 100).toFixed(1) + '%', 'text-warning') +
        sb('L (سیستم)', r.L, 'text-primary') +
        sb('Lq (صف)', r.Lq, 'text-danger') +
        sb('W (سیستم)', r.W, 'text-success') +
        sb('Wq (صف)', r.Wq, 'text-warning') +
        `</div>`;

    if (r.extra && Object.keys(r.extra).length) {
        html += '<div class="mb-3 small"><div class="fw-bold mb-1"><i class="fas fa-info-circle me-1"></i> جزئیات:</div>';
        for (const [k, v] of Object.entries(r.extra)) {
            html += `<div class="text-muted">• <strong>${escapeHtml(k)}:</strong> ${escapeHtml(String(v))}</div>`;
        }
        html += '</div>';
    }

    if (r.Pn && Object.keys(r.Pn).length) {
        html += `<h6 class="fw-bold small mb-2"><i class="fas fa-chart-bar me-1"></i> توزیع احتمال P(n)</h6>
                 <canvas id="pnChart" class="or-chart-canvas mb-3"></canvas>`;
    }
    if (data.sensitivity && data.sensitivity.length > 1) {
        html += `<h6 class="fw-bold small mb-2"><i class="fas fa-chart-line me-1"></i> تحلیل حساسیت نسبت به ρ</h6>
                 <canvas id="sensChart" class="or-chart-canvas mb-3"></canvas>`;
    }

    html += `<div class="alert alert-light border py-2 small mb-0">
        <pre class="mb-0" style="white-space: pre-wrap;">${escapeHtml(r.interpretation)}</pre></div>`;

    box.innerHTML = html;
    if (r.Pn && Object.keys(r.Pn).length) drawPnChart(r.Pn);
    if (data.sensitivity && data.sensitivity.length > 1) drawSensChart(data.sensitivity);
}

function drawPnChart(Pn) {
    const canvas = document.getElementById('pnChart');
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 240;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);
    const entries = Object.entries(Pn).slice(0, 25);
    const maxP = Math.max(...entries.map(([, v]) => v)) * 1.15;
    const pL = 40, pR = 10, pT = 10, pB = 30;
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
        if (i % Math.ceil(entries.length / 12) === 0) {
            ctx.fillStyle = '#6c757d'; ctx.textAlign = 'center';
            ctx.fillText('n=' + n, x + bw / 2, H - pB + 14);
        }
    });
}

function drawSensChart(pts) {
    const canvas = document.getElementById('sensChart');
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 240;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);
    const pL = 40, pR = 10, pT = 10, pB = 30;
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

async function deleteProject(id) {
    if (!confirm('آیا از حذف این پروژه مطمئن هستید؟')) return;
    try {
        const res = await fetch(DELETE_URL + id, { headers: {'X-Requested-With': 'XMLHttpRequest'} });
        if (res.ok) location.reload();
        else alert('خطا در حذف پروژه');
    } catch (e) { alert('خطای شبکه: ' + e.message); }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});
document.addEventListener('DOMContentLoaded', () => { renderModel(); });
</script>