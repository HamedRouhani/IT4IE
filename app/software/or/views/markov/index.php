<?php
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-project-diagram text-info me-2"></i>زنجیره مارکوف</h3>
            <small class="text-muted">مدل‌سازی قابلیت اطمینان و پیش‌بینی حالت‌های آینده</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تعریف مدل مارکوف</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">نام حالت‌ها (با کاما یا اینتر جدا کنید)</label>
                        <textarea id="statesInput" class="form-control form-control-sm" rows="3" placeholder="مثال: سالم, نیمه‌خراب, خراب">سالم
نیمه‌خراب
خراب</textarea>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="buildMatrix()">
                            <i class="fas fa-sync me-1"></i> به‌روزرسانی ماتریس
                        </button>
                    </div>

                    <div id="matrixBox" class="mb-3"></div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">تعداد گام‌های پیش‌بینی (n)</label>
                        <input type="number" id="steps" class="form-control form-control-sm" value="5" min="1" max="100">
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-info text-white" onclick="runMarkov()">
                            <i class="fas fa-play me-1"></i> حل و پیش‌بینی
                        </button>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-info flex-fill" onclick="loadPreset('reliability')">نمونه: قابلیت اطمینان</button>
                            <button class="btn btn-sm btn-outline-info flex-fill" onclick="loadPreset('market')">نمونه: بازار</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>نتایج پیش‌بینی</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-project-diagram fa-3x mb-3"></i>
                        <p class="small mb-0">حالت‌ها و ماتریس انتقال را تعریف کنید.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    $states = json_decode($p['states_json'] ?? '[]', true) ?: [];
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1 text-truncate"><?= or_e($p['name']) ?></h6>
                                <small class="text-muted d-block mb-2"><?= count($states) ?> حالت | <?= (int)$p['steps'] ?> گام</small>
                                <div class="d-flex gap-1">
                                    <a href="<?= or_url('controller=markov&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill">
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
    
function or_stat_box(label, value, valueClass = '') {
    return `<div class="border rounded bg-light text-center p-2 h-100">
        <small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">${label}</small>
        <strong class="d-block ${valueClass}" style="line-height:1.3;word-break:break-word;">${value}</strong>
    </div>`;
}

let currentStates = [];

// ✅ تابع ساخت ماتریس - باید اول تعریف شود
function buildMatrix() {
    const raw = document.getElementById('statesInput').value;
    currentStates = raw.split(/[\n,،]+/).map(s => s.trim()).filter(s => s !== '');
    
    if (currentStates.length < 2) {
        document.getElementById('matrixBox').innerHTML = '<div class="alert alert-warning small">حداقل ۲ حالت وارد کنید.</div>';
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-sm table-bordered text-center mb-0 small">';
    html += '<thead class="table-light"><tr><th>از \\ به</th>';
    currentStates.forEach(s => html += `<th>${escapeHtml(s)}</th>`);
    html += '</tr></thead><tbody>';
    
    currentStates.forEach((rowState, i) => {
        html += `<tr><td class="fw-bold text-start">${escapeHtml(rowState)}</td>`;
        currentStates.forEach((colState, j) => {
            // مقدار پیش‌فرض: اگر قطر اصلی باشد 0.9، در غیر این صورت توزیع یکنواخت
            let defVal;
            if (i === j) {
                defVal = 0.9;
            } else {
                defVal = (0.1 / (currentStates.length - 1)).toFixed(2);
            }
            html += `<td><input type="number" step="0.01" min="0" max="1" class="form-control form-control-sm p-1 prob-input" data-r="${i}" data-c="${j}" value="${defVal}"></td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table></div>';
    
    // بخش حالت اولیه
    html += '<div class="mt-3"><label class="form-label small mb-1">احتمال حالت اولیه (مجموع باید ۱ شود)</label><div class="d-flex gap-2 flex-wrap">';
    currentStates.forEach((s, i) => {
        const defInit = i === 0 ? 1.0 : 0.0;
        html += `<div class="flex-fill"><small class="d-block text-muted">${escapeHtml(s)}</small><input type="number" step="0.01" min="0" max="1" class="form-control form-control-sm p-1 init-input" data-i="${i}" value="${defInit}"></div>`;
    });
    html += '</div></div>';

    document.getElementById('matrixBox').innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function loadPreset(type) {
    if (type === 'reliability') {
        document.getElementById('statesInput').value = "سالم (Working)\nنیمه‌خراب (Degraded)\nخراب (Failed)";
        buildMatrix();
        // تنظیم مقادیر خاص قابلیت اطمینان
        setTimeout(() => {
            document.querySelectorAll('.prob-input').forEach(inp => {
                const r = parseInt(inp.dataset.r);
                const c = parseInt(inp.dataset.c);
                if (r === 0 && c === 0) inp.value = 0.95;
                else if (r === 0 && c === 1) inp.value = 0.05;
                else if (r === 0 && c === 2) inp.value = 0.00;
                else if (r === 1 && c === 0) inp.value = 0.80;
                else if (r === 1 && c === 1) inp.value = 0.15;
                else if (r === 1 && c === 2) inp.value = 0.05;
                else if (r === 2 && c === 2) inp.value = 1.00; // حالت جاذب
                else inp.value = 0.00;
            });
            document.querySelectorAll('.init-input').forEach(inp => {
                inp.value = inp.dataset.i === '0' ? 1.0 : 0.0;
            });
        }, 50);
    } else if (type === 'market') {
        document.getElementById('statesInput').value = "رکود\nثبات\nرشد";
        buildMatrix();
        setTimeout(() => {
            document.querySelectorAll('.prob-input').forEach(inp => inp.value = 0.33);
            document.querySelectorAll('.init-input').forEach(inp => inp.value = 0.33);
        }, 50);
    }
}

function getMatrixData() {
    const matrix = [];
    const initial = [];
    const n = currentStates.length;
    
    for (let i = 0; i < n; i++) {
        matrix[i] = [];
        for (let j = 0; j < n; j++) {
            const el = document.querySelector(`.prob-input[data-r="${i}"][data-c="${j}"]`);
            matrix[i][j] = parseFloat(el ? el.value : 0);
        }
        const initEl = document.querySelector(`.init-input[data-i="${i}"]`);
        initial[i] = parseFloat(initEl ? initEl.value : 0);
    }
    return { matrix, initial };
}

async function runMarkov() {
    if (currentStates.length < 2) { alert('حداقل ۲ حالت تعریف کنید.'); return; }
    const { matrix, initial } = getMatrixData();
    const steps = parseInt(document.getElementById('steps').value) || 5;
    
    const payload = {
        states: currentStates,
        matrix,
        initial,
        steps,
        save: document.getElementById('saveCheck').checked,
        name: document.getElementById('projectName').value.trim()
    };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-info"></div><p class="small mt-2">در حال محاسبه...</p></div>';

    try {
        const res = await fetch('<?= or_url('controller=markov&action=solve') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; return; }
        renderResult(data.result);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(r) {
    const box = document.getElementById('resultBox');
    let html = `<div class="alert alert-${r.converged ? 'success' : 'warning'} py-2 small mb-3">
        <i class="fas fa-info-circle me-1"></i> ${escapeHtml(r.interpretation).replace(/\n/g, '<br>')}
    </div>`;

    html += '<div class="row g-1 g-md-2 mb-3">';
    r.states.forEach((s, i) => {
        const initP = (r.initial[i] * 100).toFixed(1);
        const finalP = (r.final_state[i] * 100).toFixed(1);
        const steadyP = (r.steady_state[i] * 100).toFixed(1);
        html += `<div class="col-12 col-md-6 col-lg-4">
            <div class="border rounded p-2 h-100">
                <div class="fw-bold small mb-2 text-truncate">${escapeHtml(s)}</div>
                <div class="row g-1 text-center small">
                    <div class="col-4">${or_stat_box('اولیه', initP + '%')}</div>
                    <div class="col-4">${or_stat_box('گام n', finalP + '%', 'text-info')}</div>
                    <div class="col-4">${or_stat_box('پایدار', steadyP + '%', 'text-success')}</div>
                </div>
            </div>
        </div>`;
    });
    html += '</div>';

    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-chart-bar me-1"></i>نمودار تحول احتمالات</h6>
             <canvas id="markovChart" style="width:100%;height:260px;display:block;" class="mb-3"></canvas>`;

    box.innerHTML = html;
    drawChart(r);
}

function drawChart(r) {
    const canvas = document.getElementById('markovChart');
    if (!canvas) return;
    const dpr = window.devicePixelRatio || 1;
    const W = canvas.clientWidth || 700, H = 260;
    canvas.width = W * dpr; canvas.height = H * dpr;
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, W, H);

    const n = r.states.length;
    const stepsToShow = Math.min(r.steps, 10);
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
        ctx.beginPath();
        ctx.strokeStyle = colors[s % colors.length];
        ctx.lineWidth = 2;
        for (let step = 0; step <= stepsToShow; step++) {
            const prob = r.history[step] ? r.history[step][s] : r.steady_state[s];
            const x = pL + step * xStep;
            const y = H - pB - (prob * (H - pT - pB));
            if (step === 0) ctx.moveTo(x, y); else ctx.lineTo(x, y);
        }
        ctx.stroke();
        
        const lastProb = r.history[stepsToShow] ? r.history[stepsToShow][s] : r.steady_state[s];
        ctx.fillStyle = colors[s % colors.length];
        ctx.beginPath();
        ctx.arc(pL + stepsToShow * xStep, H - pB - (lastProb * (H - pT - pB)), 4, 0, Math.PI * 2);
        ctx.fill();
    }

    let ly = 20;
    for (let s = 0; s < n; s++) {
        ctx.fillStyle = colors[s % colors.length];
        ctx.fillRect(W - 130, ly, 12, 12);
        ctx.fillStyle = '#333'; ctx.textAlign = 'left'; ctx.font = '10px sans-serif';
        ctx.fillText(r.states[s].substring(0, 15), W - 114, ly + 10);
        ly += 18;
    }
}

async function deleteProject(id) {
    if (!confirm('حذف شود؟')) return;
    try {
        const res = await fetch('<?= or_url('controller=markov&action=delete&id=') ?>' + id);
        if (res.ok) location.reload();
        else alert('خطا در حذف');
    } catch (e) { alert('خطای شبکه: ' + e.message); }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});

// ✅ اجرای اولیه هنگام لود صفحه
document.addEventListener('DOMContentLoaded', () => { 
    buildMatrix(); 
});
</script>