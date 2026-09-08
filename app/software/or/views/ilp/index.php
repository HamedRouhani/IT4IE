<?php
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-cubes text-danger me-2"></i>برنامه‌ریزی خطی عدد صحیح (ILP)</h3>
            <small class="text-muted">حل مسائل بهینه‌سازی با قید عدد صحیح متغیرها - الگوریتم Branch and Bound</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تعریف مسئله ILP</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small mb-1">نوع بهینه‌سازی</label>
                            <select id="sense" class="form-select form-select-sm">
                                <option value="maximize">ماکزیمم</option>
                                <option value="minimize">مینیمم</option>
                            </select>
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">متغیرها (n)</label>
                            <input type="number" id="numVars" class="form-control form-control-sm" value="2" min="1" max="8">
                        </div>
                        <div class="col-3">
                            <label class="form-label small mb-1">محدودیت‌ها (m)</label>
                            <input type="number" id="numConstraints" class="form-control form-control-sm" value="2" min="1" max="10">
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-3" onclick="buildILPForm()">
                        <i class="fas fa-sync me-1"></i> به‌روزرسانی فرم
                    </button>

                    <div id="dynamicForm" class="mb-3"></div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه">
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-danger text-white fw-bold" onclick="solveILP()">
                            <i class="fas fa-project-diagram me-1"></i> حل با Branch & Bound
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="loadPreset()">
                            <i class="fas fa-magic me-1"></i> بارگذاری نمونه (تولید محصول)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-danger text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>نتایج بهینه‌سازی عدد صحیح</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-cubes fa-3x mb-3"></i>
                        <p class="small mb-0">مسئله ILP را تعریف و حل کنید.</p>
                        <small class="text-muted d-block mt-2">ILP برای مسائلی کاربرد دارد که جواب کسری معنی ندارد<br>(مثل تعداد محصول، تعداد کارگر، تعداد ماشین)</small>
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
                    $hasResult = !empty($r) && ($r['status'] ?? '') === 'optimal';
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100 <?= $hasResult ? 'border-success' : '' ?>">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <div class="d-flex gap-1 mb-2">
                                    <span class="badge bg-<?= $hasResult ? 'success' : 'secondary' ?>">
                                        <?= $hasResult ? 'حل‌شده' : 'پیش‌نویس' ?>
                                    </span>
                                    <span class="badge bg-info"><?= strtoupper($p['sense'] ?? 'MAX') ?></span>
                                </div>
                                <?php if ($hasResult): ?>
                                    <div class="row g-1 mb-2">
                                        <div class="col-6">
                                            <div class="border rounded bg-light text-center p-1 h-100">
                                                <small class="d-block text-muted" style="font-size:.7rem;">هدف</small>
                                                <strong class="d-block" style="font-size:.85rem;"><?= number_format((float)($r['objective_value'] ?? 0), 2) ?></strong>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded bg-light text-center p-1 h-100">
                                                <small class="d-block text-muted" style="font-size:.7rem;">گره‌ها</small>
                                                <strong class="d-block" style="font-size:.85rem;"><?= $r['nodes_explored'] ?? 0 ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex gap-1 mt-2">
                                    <?php if ($hasResult): ?>
                                        <a href="<?= or_url('controller=ilp&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill" title="مشاهده">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= or_url('controller=ilp&action=edit&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-primary flex-fill" title="ویرایش">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProject(<?= (int)$p['id'] ?>)" title="حذف">
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
        <small class="d-block text-muted mb-1" style="font-size:.72rem;">${label}</small>
        <strong class="d-block ${valueClass}" style="line-height:1.3;">${value}</strong>
    </div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function buildILPForm() {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;

    let html = '<div class="mb-3"><label class="form-label small mb-1">ضرایب تابع هدف (c)</label><div class="d-flex gap-2 flex-wrap">';
    for (let j = 0; j < n; j++) {
        html += `<div class="flex-fill">
            <small class="d-block text-muted">x${j+1}</small>
            <input type="number" step="any" class="form-control form-control-sm p-1 c-input" data-j="${j}" value="0">
            <div class="form-check mt-1">
                <input type="checkbox" class="form-check-input int-var-check" data-j="${j}" id="intVar${j}" checked>
                <label class="form-check-label" for="intVar${j}" style="font-size:.7rem;">صحیح</label>
            </div>
        </div>`;
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
    buildILPForm();

    setTimeout(() => {
        // Max Z = 5x1 + 4x2 (سود دو محصول)
        document.querySelector('.c-input[data-j="0"]').value = 5;
        document.querySelector('.c-input[data-j="1"]').value = 4;

        // محدودیت ۱: 6x1 + 4x2 <= 24 (مواد اولیه)
        document.querySelector('.a-input[data-i="0"][data-j="0"]').value = 6;
        document.querySelector('.a-input[data-i="0"][data-j="1"]').value = 4;
        document.querySelector('.constraint-type[data-i="0"]').value = '<=';
        document.querySelector('.b-input[data-i="0"]').value = 24;

        // محدودیت ۲: x1 + 2x2 <= 6 (نیروی کار)
        document.querySelector('.a-input[data-i="1"][data-j="0"]').value = 1;
        document.querySelector('.a-input[data-i="1"][data-j="1"]').value = 2;
        document.querySelector('.constraint-type[data-i="1"]').value = '<=';
        document.querySelector('.b-input[data-i="1"]').value = 6;

        // محدودیت ۳: x1 + x2 <= 5 (ظرفیت انبار)
        document.querySelector('.a-input[data-i="2"][data-j="0"]').value = 1;
        document.querySelector('.a-input[data-i="2"][data-j="1"]').value = 1;
        document.querySelector('.constraint-type[data-i="2"]').value = '<=';
        document.querySelector('.b-input[data-i="2"]').value = 5;
    }, 50);
}

function getProblemData() {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;

    const c = [];
    const integer_vars = [];
    for (let j = 0; j < n; j++) {
        c.push(parseFloat(document.querySelector(`.c-input[data-j="${j}"]`).value) || 0);
        const chk = document.querySelector(`.int-var-check[data-j="${j}"]`);
        if (chk && chk.checked) integer_vars.push(j);
    }

    const A = [], b = [], constraints_types = [];
    for (let i = 0; i < m; i++) {
        const row = [];
        for (let j = 0; j < n; j++) {
            row.push(parseFloat(document.querySelector(`.a-input[data-i="${i}"][data-j="${j}"]`).value) || 0);
        }
        A.push(row);
        b.push(parseFloat(document.querySelector(`.b-input[data-i="${i}"]`).value) || 0);
        constraints_types.push(document.querySelector(`.constraint-type[data-i="${i}"]`).value);
    }

    return { c, A, b, constraints_types, integer_vars, sense: document.getElementById('sense').value };
}

async function solveILP() {
    const data = getProblemData();
    if (data.integer_vars.length === 0) {
        alert('️ حداقل یک متغیر باید عدد صحیح باشد.');
        return;
    }

    const payload = {
        ...data,
        save: document.getElementById('saveCheck').checked,
        name: document.getElementById('projectName').value.trim()
    };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-danger"></div><p class="small mt-2">در حال حل با Branch & Bound...</p></div>';

    try {
        const res = await fetch('<?= or_url('controller=ilp&action=solve') ?>', {
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
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(r) {
    const box = document.getElementById('resultBox');
    let html = `<div class="alert alert-success py-2 small mb-3">
        <i class="fas fa-check-circle me-1"></i> ${escapeHtml(r.interpretation).replace(/\n/g, '<br>')}
    </div>`;

    html += `<div class="row g-1 g-md-2 mb-3">
        <div class="col-6 col-md-4">${or_stat_box('مقدار تابع هدف', r.objective_value, 'fw-bold text-danger')}</div>
        <div class="col-6 col-md-4">${or_stat_box('وضعیت', 'بهینه ILP', 'text-success')}</div>
        <div class="col-6 col-md-4">${or_stat_box('گره‌های بررسی‌شده', r.nodes_explored)}</div>
    </div>`;

    html += `<h6 class="fw-bold small mb-2 mt-3"><i class="fas fa-calculator me-1 text-info"></i>مقادیر بهینه متغیرها</h6>
             <div class="row g-1 g-md-2 mb-3">`;
    r.solution.forEach((val, i) => {
        const isInt = r.integer_vars.includes(i);
        html += `<div class="col-6 col-md-3">${or_stat_box('x' + (i+1) + (isInt ? ' 🔢' : ''), val, isInt ? 'text-success fw-bold' : 'text-muted')}</div>`;
    });
    html += `</div>`;

    if (r.branching_log && r.branching_log.length > 0) {
        html += `<h6 class="fw-bold small mb-2 mt-3"><i class="fas fa-project-diagram me-1 text-warning"></i>لاگ Branch & Bound (${r.branching_log.length} مرحله)</h6>
                 <div class="table-responsive"><table class="table table-sm table-bordered small mb-0">
                 <thead class="table-light"><tr><th>گره</th><th>عمق</th><th>رویداد</th></tr></thead><tbody>`;
        r.branching_log.slice(0, 15).forEach(log => {
            html += `<tr><td>${log.node}</td><td>${log.depth}</td><td>${escapeHtml(log.message)}</td></tr>`;
        });
        html += `</tbody></table></div>`;
        if (r.branching_log.length > 15) {
            html += `<small class="text-muted">... و ${r.branching_log.length - 15} مرحله دیگر</small>`;
        }
    }

    box.innerHTML = html;
}

async function deleteProject(id) {
    if (!confirm('حذف شود؟')) return;
    try {
        const res = await fetch('<?= or_url('controller=ilp&action=delete&id=') ?>' + id);
        if (res.ok) location.reload();
        else alert('خطا در حذف');
    } catch (e) { alert('خطای شبکه: ' + e.message); }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});

document.addEventListener('DOMContentLoaded', () => { buildILPForm(); });
</script>