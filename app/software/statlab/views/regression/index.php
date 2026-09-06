<?php
/**
 * StatLab - رگرسیون و همبستگی (با قابلیت استفاده از داده‌های پروژه موجود)
 */
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="statlab-page-header">
        <h3 class="mb-0"><i class="fas fa-chart-line text-success me-2"></i>رگرسیون و همبستگی</h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- ═══ فرم ═══ -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تنظیمات تحلیل</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small mb-1">نوع تحلیل</label>
                            <select id="mode" class="form-select form-select-sm" onchange="renderMode()">
                                <option value="simple">رگرسیون خطی ساده</option>
                                <option value="correlation">همبستگی پیرسون/اسپیرمن</option>
                                <option value="multiple">رگرسیون چندگانه</option>
                            </select>
                        </div>
                        <div class="col-5" id="kBox" style="display:none;">
                            <label class="form-label small mb-1">تعداد پیشبین‌ها</label>
                            <input type="number" id="k" class="form-control form-control-sm" min="1" max="5" value="2" onchange="renderMode()">
                        </div>
                    </div>

                    <!-- ═══ بارگذاری داده از پروژه موجود ═══ -->
                    <div class="p-2 mb-3 border rounded" style="background: #f0fdf4;">
                        <label class="form-label fw-bold small mb-1">
                            <i class="fas fa-database me-1 text-success"></i> استفاده از داده‌های پروژه موجود
                        </label>
                        <div class="d-flex gap-2 mb-2">
                            <select id="sourceProject" class="form-select form-select-sm">
                                <option value="0">انتخاب پروژه...</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-success text-nowrap" onclick="loadProjectDatasets()">
                                <i class="fas fa-download me-1"></i> بارگذاری
                            </button>
                        </div>
                        <div id="datasetPicker" class="small"></div>
                    </div>

                    <div id="inputsBox"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">سطح α</label>
                            <select id="alpha" class="form-select form-select-sm">
                                <option value="0.05">0.05</option>
                                <option value="0.01">0.01</option>
                                <option value="0.10">0.10</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">ذخیره نتیجه در</label>
                            <select id="targetProject" class="form-select form-select-sm" onchange="onTargetChange()">
                                <option value="0">➕ پروژه جدید (ایجاد خودکار)</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه جدید (اختیاری)">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-success" onclick="runRegression()">
                            <i class="fas fa-play me-1"></i> اجرای تحلیل
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="loadSample()">
                            <i class="fas fa-magic me-1"></i> داده نمونه
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ نتایج ═══ -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-poll me-2"></i>نتایج</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chart-line fa-3x mb-3"></i>
                        <p class="small mb-0">داده‌ها را دستی وارد کنید یا از پروژه موجود بارگذاری کنید، سپس «اجرای تحلیل» را بزنید.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const PROJECTS = <?= json_encode($projects, JSON_UNESCAPED_UNICODE) ?>;
let loadedDatasets = [];
let lastSimpleResult = null;

function ta(key, label) {
    return `<div class="mb-2"><label class="form-label small mb-1">${label}</label>
        <textarea id="in_${key}" class="form-control form-control-sm font-monospace" rows="4"
        placeholder="دستی وارد کنید یا از پروژه بارگذاری کنید"></textarea></div>`;
}

function renderMode() {
    const mode = document.getElementById('mode').value;
    const isMulti = mode === 'multiple';
    document.getElementById('kBox').style.display = isMulti ? '' : 'none';
    const k = isMulti ? Math.min(5, Math.max(1, parseInt(document.getElementById('k').value) || 1)) : 1;

    let html = ta('y', 'Y (متغیر وابسته)');
    for (let j = 1; j <= k; j++) html += ta('x' + j, 'X' + j + (isMulti ? ' (پیشبین)' : ' (متغیر مستقل)'));
    document.getElementById('inputsBox').innerHTML = html;
    renderDatasetPicker();
}

function onTargetChange() {
    const isNew = (parseInt(document.getElementById('targetProject').value) || 0) === 0;
    document.getElementById('projectName').disabled = !isNew;
}

// ═══ بارگذاری datasets از پروژه موجود ═══
async function loadProjectDatasets() {
    const pid = parseInt(document.getElementById('sourceProject').value) || 0;
    if (!pid) { showToast('⚠️ ابتدا یک پروژه انتخاب کنید.', 'warning'); return; }

    try {
        const res = await fetch('<?= stat_url('controller=project&action=datasets&id=') ?>' + pid);
        const data = await res.json();
        if (!data.success) { showToast('❌ ' + data.error, 'danger'); return; }

        loadedDatasets = data.datasets;
        renderDatasetPicker();

        // تنظیم خودکار پروژه مقصد = پروژه منبع (ذخیره result در همان پروژه)
        document.getElementById('targetProject').value = String(pid);
        onTargetChange();
        showToast(`✅ ${loadedDatasets.length} مجموعه داده بارگذاری شد`);
    } catch (e) {
        showToast('❌ خطای شبکه: ' + e.message, 'danger');
    }
}

// ═══ نمایش دکمه‌های جایگذاری ═══
function renderDatasetPicker() {
    const box = document.getElementById('datasetPicker');
    if (!loadedDatasets.length) { box.innerHTML = ''; return; }

    const mode = document.getElementById('mode').value;
    const isMulti = mode === 'multiple';
    const k = isMulti ? Math.min(5, Math.max(1, parseInt(document.getElementById('k').value) || 1)) : 1;

    const targets = [{key:'y', label:'Y (وابسته)'}];
    for (let j = 1; j <= k; j++) targets.push({key: 'x' + j, label: 'X' + j});

    let html = '<small class="text-muted d-block mb-1">برای هر dataset، مقصد را انتخاب کنید:</small>';
    loadedDatasets.forEach((ds, idx) => {
        html += `<div class="d-flex flex-wrap align-items-center gap-1 mb-1 p-1 border rounded bg-white">
            <span class="fw-bold flex-grow-1" style="min-width:100px;">
                ${escapeHtml(ds.name)} <span class="badge bg-secondary">${ds.data.length}</span>
            </span>`;
        targets.forEach(tg => {
            html += `<button type="button" class="btn btn-sm btn-outline-primary py-0"
                        onclick="assignDataset(${idx}, '${tg.key}')">→ ${escapeHtml(tg.label)}</button>`;
        });
        html += '</div>';
    });
    box.innerHTML = html;
}

function assignDataset(idx, key) {
    const ds = loadedDatasets[idx];
    const el = document.getElementById('in_' + key);
    if (!el || !ds) return;
    el.value = ds.data.join('\n');
    showToast(`✅ «${escapeHtml(ds.name)}» در «${escapeHtml(key)}» قرار گرفت`);
}

function loadSample() {
    document.getElementById('mode').value = 'simple';
    renderMode();
    document.getElementById('in_x1').value = [2,3,4,5,6,7,8,9,10,11].join('\n');
    document.getElementById('in_y').value  = [55,60,65,70,74,80,85,88,92,97].join('\n');
    showToast('✅ داده نمونه بارگذاری شد');
}

async function runRegression() {
    const mode = document.getElementById('mode').value;
    const payload = {
        mode,
        alpha: parseFloat(document.getElementById('alpha').value),
        k: parseInt(document.getElementById('k').value) || 1,
        project_id: parseInt(document.getElementById('targetProject').value) || 0,
        project_name: document.getElementById('projectName').value.trim(),
        y: document.getElementById('in_y').value,
        save_datasets: loadedDatasets.length === 0,  // فقط اگر دستی وارد شده، dataset ایجاد کن
    };
    const xCount = (mode === 'multiple') ? payload.k : 1;
    for (let j = 1; j <= xCount; j++) payload['x' + j] = document.getElementById('in_x' + j).value;

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success"></div><p class="small mt-2">در حال محاسبه...</p></div>';

    try {
        const res = await fetch('<?= stat_url('controller=regression&action=analyze') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) {
            box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`;
            return;
        }
        renderResult(data);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function escapeHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed`;
    t.style.cssText = 'top:80px;left:50%;transform:translateX(-50%);z-index:10000;min-width:280px;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.transition='opacity .3s'; t.style.opacity='0'; setTimeout(() => t.remove(), 300); }, 2500);
}
function sb(label, value, cls = '') {
    return `<div class="col-6 col-md-3"><div class="stat-box"><small>${label}</small><strong class="${cls}">${value}</strong></div></div>`;
}
function sigStar(p) { return p < 0.001 ? '***' : (p < 0.01 ? '**' : (p < 0.05 ? '*' : '')); }

function renderResult(data) {
    const r = data.result;
    const box = document.getElementById('resultBox');
    let html = '';

    // ✅ دکمه مشاهده پروژه
    html += `<div class="alert alert-success py-2 mb-3 small">
        <i class="fas fa-check-circle me-1"></i>
        تحلیل در پروژه «<strong>${escapeHtml(data.project_name)}</strong>» ذخیره شد.
        <a href="<?= stat_url('controller=project&action=show&id=') ?>${data.project_id}" class="alert-link ms-2">مشاهده پروژه →</a>
    </div>`;

    if (data.mode === 'correlation') {
        html += `<div class="row g-1 g-md-2 mb-3">` +
            sb('پیرسون r', Number(r.pearson_r).toFixed(4), 'text-primary') +
            sb('p-value پیرسون', Number(r.pearson_p).toFixed(5), r.pearson_p < r.alpha ? 'text-success' : 'text-danger') +
            sb('اسپیرمن ρ', Number(r.spearman_rho).toFixed(4), 'text-info') +
            sb('p-value اسپیرمن', Number(r.spearman_p).toFixed(5), r.spearman_p < r.alpha ? 'text-success' : 'text-danger') +
            `</div>` +
            `<div class="alert ${r.conclusion === 'reject' ? 'alert-success' : 'alert-warning'} py-2 small mb-2">
                شدت رابطه: <strong>${escapeHtml(r.strength)}</strong> | n=${r.n}</div>`;
    }

    if (data.mode === 'simple') {
        html += `<div class="alert alert-info py-2 small mb-3">
            <strong>مدل برآوردی:</strong> <span dir="ltr" class="font-monospace">ŷ = ${Number(r.intercept).toFixed(3)} + ${Number(r.slope).toFixed(3)}·x</span></div>`;
        html += `<div class="row g-1 g-md-2 mb-3">` +
            sb('R²', (Number(r.r2) * 100).toFixed(1) + '%', 'text-primary') +
            sb('شیب (b₁)', Number(r.slope).toFixed(4) + sigStar(r.p_slope), 'text-success') +
            sb('عرض (b₀)', Number(r.intercept).toFixed(4) + sigStar(r.p_intercept)) +
            sb('خطای معیار (Se)', Number(r.se).toFixed(4)) +
            sb('F مدل', Number(r.F).toFixed(2) + sigStar(r.p_F), 'text-warning') +
            sb('p-value شیب', Number(r.p_slope).toFixed(5), r.p_slope < r.alpha ? 'text-success' : 'text-danger') +
            sb('CI 95% شیب', `<span dir="ltr">[${r.ci_slope[0]}, ${r.ci_slope[1]}]</span>`) +
            sb('n', r.n) +
            `</div>`;
        html += `<canvas id="scatterCanvas" style="width:100%;height:auto;max-height:380px;"></canvas>`;
        lastSimpleResult = r;
    }

    if (data.mode === 'multiple') {
        html += `<div class="row g-1 g-md-2 mb-3">` +
            sb('R²', (Number(r.r2) * 100).toFixed(1) + '%', 'text-primary') +
            sb('R² تعدیل‌شده', (Number(r.adj_r2) * 100).toFixed(1) + '%', 'text-primary') +
            sb('F', Number(r.F).toFixed(2) + sigStar(r.p_F), 'text-warning') +
            sb('p-value مدل', Number(r.p_F).toFixed(5), r.p_F < r.alpha ? 'text-success' : 'text-danger') +
            `</div>`;
        html += `<div class="table-responsive"><table class="table table-sm table-hover small mb-2">
            <thead class="table-light"><tr><th>ضریب</th><th>برآورد</th><th>خطای معیار</th><th>t</th><th>p</th><th>معناداری</th></tr></thead><tbody>`;
        r.coefficients.forEach(c => {
            html += `<tr><td class="fw-bold">${escapeHtml(c.name)}</td>
                <td class="font-monospace">${Number(c.beta).toFixed(4)}</td>
                <td class="font-monospace">${Number(c.se).toFixed(4)}</td>
                <td class="font-monospace">${Number(c.t).toFixed(3)}</td>
                <td class="font-monospace">${Number(c.p).toFixed(5)}</td>
                <td>${c.sig ? '<span class="badge bg-success">معنادار ' + sigStar(c.p) + '</span>' : '<span class="badge bg-secondary">نامعنادر</span>'}</td></tr>`;
        });
        html += `</tbody></table></div>`;
    }

    html += `<div class="alert ${r.conclusion === 'reject' ? 'alert-success' : 'alert-warning'} py-2 mt-2 mb-0">
        <pre class="mb-0 small" style="white-space: pre-wrap;">${escapeHtml(r.interpretation_fa)}</pre></div>`;

    box.innerHTML = html;
    if (data.mode === 'simple') drawScatter(r);
}

// ═══ نمودار پراکنش ═══
function drawScatter(r) {
    const canvas = document.getElementById('scatterCanvas');
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 360;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const pts = r.points;
    const xs = pts.map(p => p.x), ys = pts.map(p => p.y);
    const minX = Math.min(...xs), maxX = Math.max(...xs);
    const minY = Math.min(...ys), maxY = Math.max(...ys);
    const padL = 48, padR = 14, padT = 12, padB = 32;
    const sx = x => padL + (x - minX) / ((maxX - minX) || 1) * (W - padL - padR);
    const sy = y => H - padB - (y - minY) / ((maxY - minY) || 1) * (H - padT - padB);

    ctx.strokeStyle = '#dee2e6'; ctx.lineWidth = 1;
    ctx.beginPath(); ctx.moveTo(padL, padT); ctx.lineTo(padL, H - padB); ctx.lineTo(W - padR, H - padB); ctx.stroke();
    ctx.fillStyle = '#6c757d'; ctx.font = '10px sans-serif'; ctx.textAlign = 'center';
    for (let i = 0; i <= 5; i++) {
        const vx = minX + (maxX - minX) * i / 5;
        ctx.fillText(Number(vx.toFixed(1)), sx(vx), H - padB + 14);
    }
    ctx.textAlign = 'right';
    for (let i = 0; i <= 4; i++) {
        const vy = minY + (maxY - minY) * i / 4;
        ctx.fillText(Number(vy.toFixed(1)), padL - 6, sy(vy) + 3);
    }

    ctx.strokeStyle = '#198754'; ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(sx(minX), sy(r.intercept + r.slope * minX));
    ctx.lineTo(sx(maxX), sy(r.intercept + r.slope * maxX));
    ctx.stroke();

    ctx.fillStyle = '#0d6efd';
    pts.forEach(p => {
        ctx.beginPath();
        ctx.arc(sx(p.x), sy(p.y), 4, 0, Math.PI * 2);
        ctx.fill();
    });
}

window.addEventListener('resize', () => {
    if (lastSimpleResult) drawScatter(lastSimpleResult);
});

document.addEventListener('DOMContentLoaded', () => { renderMode(); onTargetChange(); });
</script>