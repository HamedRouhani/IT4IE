<?php
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-balance-scale-right text-warning me-2"></i>روش دوگان و تحلیل اقتصادی</h3>
            <small class="text-muted">محاسبه قیمت‌های سایه‌ای، هزینه‌های کاهش‌یافته و تحلیل حساسیت</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تعریف مسئله</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small mb-1">نوع بهینه‌سازی</label>
                            <select id="sense" class="form-select form-select-sm">
                                <option value="maximize">ماکزیمم (Maximize)</option>
                                <option value="minimize">مینیمم (Minimize)</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">متغیرها (n)</label>
                            <input type="number" id="numVars" class="form-control form-control-sm" value="2" min="1" max="10">
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">محدودیت‌ها (m)</label>
                            <input type="number" id="numConstraints" class="form-control form-control-sm" value="2" min="1" max="10">
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-3" onclick="buildDualForm()">
                        <i class="fas fa-sync me-1"></i> به‌روزرسانی فرم ورودی
                    </button>

                    <div id="dynamicForm" class="mb-3"></div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه (اختیاری)">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-warning text-dark fw-bold" onclick="solveDual()">
                            <i class="fas fa-calculator me-1"></i> حل و تحلیل اقتصادی
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="loadPreset()">
                            <i class="fas fa-magic me-1"></i> بارگذاری نمونه آماده (تولید)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>نتایج تحلیل اقتصادی</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-balance-scale-right fa-3x mb-3"></i>
                        <p class="small mb-0">مسئله را تعریف و «حل و تحلیل اقتصادی» را بزنید.</p>
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
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <div class="row g-1 mb-2">
                                    <div class="col-6">
                                        <div class="border rounded bg-light text-center p-2 h-100">
                                            <small class="d-block text-muted mb-1">هدف</small>
                                            <strong class="d-block"><?= number_format((float)($r['objective_value'] ?? 0), 2) ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded bg-light text-center p-2 h-100">
                                            <small class="d-block text-muted mb-1">وضعیت</small>
                                            <strong class="d-block <?= ($r['status'] ?? '') === 'optimal' ? 'text-success' : 'text-danger' ?>">
                                                <?= ($r['status'] ?? '') === 'optimal' ? 'بهینه' : 'خطا' ?>
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1 mt-2">
                                    <a href="<?= or_url('controller=dual&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill">
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

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function buildDualForm() {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;
    
    let html = '<div class="mb-3"><label class="form-label small mb-1">ضرایب تابع هدف (c<sub>j</sub>)</label><div class="d-flex gap-2 flex-wrap">';
    for (let j = 0; j < n; j++) {
        html += `<div class="flex-fill"><small class="d-block text-muted">x${j+1}</small><input type="number" step="any" class="form-control form-control-sm p-1 c-input" data-j="${j}" value="0"></div>`;
    }
    html += '</div></div>';

    html += '<label class="form-label small mb-1">محدودیت‌ها</label>';
    for (let i = 0; i < m; i++) {
        html += `<div class="input-group input-group-sm mb-2">`;
        for (let j = 0; j < n; j++) {
            html += `<input type="number" step="any" class="form-control a-input" data-i="${i}" data-j="${j}" placeholder="a${i+1}${j+1}" value="0">`;
        }
        html += `<select class="form-select constraint-type" data-i="${i}" style="max-width: 60px;">
                    <option value="<=">≤</option>
                    <option value=">=">≥</option>
                    <option value="=">=</option>
                 </select>`;
        html += `<input type="number" step="any" class="form-control b-input" data-i="${i}" placeholder="b${i+1}" value="0">`;
        html += `</div>`;
    }
    
    document.getElementById('dynamicForm').innerHTML = html;
}

function loadPreset() {
    document.getElementById('sense').value = 'maximize';
    document.getElementById('numVars').value = 2;
    document.getElementById('numConstraints').value = 3;
    buildDualForm();
    
    setTimeout(() => {
        document.querySelector('.c-input[data-j="0"]').value = 3;
        document.querySelector('.c-input[data-j="1"]').value = 5;
        
        document.querySelector('.a-input[data-i="0"][data-j="0"]').value = 1;
        document.querySelector('.a-input[data-i="0"][data-j="1"]').value = 0;
        document.querySelector('.constraint-type[data-i="0"]').value = '<=';
        document.querySelector('.b-input[data-i="0"]').value = 4;
        
        document.querySelector('.a-input[data-i="1"][data-j="0"]').value = 0;
        document.querySelector('.a-input[data-i="1"][data-j="1"]').value = 2;
        document.querySelector('.constraint-type[data-i="1"]').value = '<=';
        document.querySelector('.b-input[data-i="1"]').value = 12;
        
        document.querySelector('.a-input[data-i="2"][data-j="0"]').value = 3;
        document.querySelector('.a-input[data-i="2"][data-j="1"]').value = 2;
        document.querySelector('.constraint-type[data-i="2"]').value = '<=';
        document.querySelector('.b-input[data-i="2"]').value = 18;
    }, 50);
}

function getProblemData() {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;
    
    const c = [];
    for (let j = 0; j < n; j++) {
        c.push(parseFloat(document.querySelector(`.c-input[data-j="${j}"]`).value) || 0);
    }
    
    const A = [];
    const b = [];
    const constraints_types = [];
    
    for (let i = 0; i < m; i++) {
        const row = [];
        for (let j = 0; j < n; j++) {
            row.push(parseFloat(document.querySelector(`.a-input[data-i="${i}"][data-j="${j}"]`).value) || 0);
        }
        A.push(row);
        b.push(parseFloat(document.querySelector(`.b-input[data-i="${i}"]`).value) || 0);
        constraints_types.push(document.querySelector(`.constraint-type[data-i="${i}"]`).value);
    }
    
    return { c, A, b, constraints_types, sense: document.getElementById('sense').value };
}

async function solveDual() {
    const { c, A, b, constraints_types, sense } = getProblemData();
    const payload = {
        c, A, b, constraints_types, sense,
        save: document.getElementById('saveCheck').checked,
        name: document.getElementById('projectName').value.trim()
    };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning"></div><p class="small mt-2">در حال حل و تحلیل...</p></div>';

    try {
        const res = await fetch('<?= or_url('controller=dual&action=solve') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { 
            box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; 
            return; 
        }
        renderResult(data.result);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small"> خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(r) {
    const box = document.getElementById('resultBox');
    let html = `<div class="alert alert-success py-2 small mb-3">
        <i class="fas fa-check-circle me-1"></i> ${escapeHtml(r.interpretation).replace(/\n/g, '<br>')}
    </div>`;

    html += `<div class="row g-1 g-md-2 mb-3">
        <div class="col-6 col-md-4">${or_stat_box('مقدار تابع هدف', r.objective_value, 'fw-bold text-primary')}</div>
        <div class="col-6 col-md-4">${or_stat_box('وضعیت', 'بهینه (Optimal)', 'text-success')}</div>
        <div class="col-6 col-md-4">${or_stat_box('نوع', r.sense === 'maximize' ? 'ماکزیمم' : 'مینیمم')}</div>
    </div>`;

    html += `<h6 class="fw-bold small mb-2 mt-3"><i class="fas fa-coins me-1 text-warning"></i>قیمت‌های سایه‌ای (Shadow Prices)</h6>
             <div class="row g-1 g-md-2 mb-3">`;
    r.shadow_prices.forEach((price, i) => {
        html += `<div class="col-6 col-md-3">${or_stat_box('محدودیت ' + (i+1), price, Math.abs(price) > 0.001 ? 'text-danger fw-bold' : 'text-muted')}</div>`;
    });
    html += `</div><small class="text-muted d-block mb-3">* ارزش نهایی یک واحد اضافی از منبع (RHS).</small>`;

    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-arrow-down me-1 text-info"></i>هزینه‌های کاهش‌یافته (Reduced Costs)</h6>
             <div class="row g-1 g-md-2 mb-3">`;
    r.reduced_costs.forEach((cost, i) => {
        html += `<div class="col-6 col-md-3">${or_stat_box('متغیر x' + (i+1), cost, Math.abs(cost) > 0.001 ? 'text-warning fw-bold' : 'text-muted')}</div>`;
    });
    html += `</div><small class="text-muted d-block">* میزان بهبود لازم در ضریب تابع هدف برای ورود متغیر به پایه.</small>`;

    box.innerHTML = html;
}

async function deleteProject(id) {
    if (!confirm('آیا از حذف این پروژه مطمئن هستید؟')) return;
    try {
        const res = await fetch('<?= or_url('controller=dual&action=delete&id=') ?>' + id);
        if (res.ok) location.reload();
        else alert('خطا در حذف پروژه');
    } catch (e) { alert('خطای شبکه: ' + e.message); }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});

document.addEventListener('DOMContentLoaded', () => { 
    buildDualForm(); 
});
</script>