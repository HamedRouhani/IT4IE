<?php
/**
 * OR Analyzer - ویرایش پروژه ILP
 * مسیر: app/software/or/views/ilp/edit.php
 */

$project = $project ?? [];
$c = $c ?? [];
$A = $A ?? [];
$b = $b ?? [];
$constraints_types = $constraints_types ?? [];
$integer_vars = $integer_vars ?? [];
$n = count($c);
$m = count($A);
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-edit text-primary me-2"></i>ویرایش پروژه ILP</h3>
            <small class="text-muted">پروژه: <?= or_e($project['name']) ?></small>
        </div>
        <a href="<?= or_url('controller=ilp&action=show&id=' . (int)$project['id']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 py-md-3">
            <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تعریف مسئله</h6>
        </div>
        <div class="card-body p-3">
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold small mb-1">نوع بهینه‌سازی</label>
                    <select id="sense" class="form-select form-select-sm">
                        <option value="maximize" <?= ($project['sense'] ?? '') === 'maximize' ? 'selected' : '' ?>>ماکزیمم</option>
                        <option value="minimize" <?= ($project['sense'] ?? '') === 'minimize' ? 'selected' : '' ?>>مینیمم</option>
                    </select>
                </div>
                <div class="col-3">
                    <label class="form-label small mb-1">متغیرها (n)</label>
                    <input type="number" id="numVars" class="form-control form-control-sm" value="<?= $n ?>" min="1" max="8" readonly>
                </div>
                <div class="col-3">
                    <label class="form-label small mb-1">محدودیت‌ها (m)</label>
                    <input type="number" id="numConstraints" class="form-control form-control-sm" value="<?= $m ?>" min="1" max="10" readonly>
                </div>
            </div>

            <div id="dynamicForm" class="mb-3"></div>

            <div class="d-grid">
                <button class="btn btn-primary fw-bold" onclick="updateILP(<?= (int)$project['id'] ?>)">
                    <i class="fas fa-save me-1"></i> ذخیره تغییرات و حل مجدد
                </button>
            </div>
        </div>
    </div>
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

function buildEditForm() {
    const n = parseInt(document.getElementById('numVars').value) || <?= $n ?>;
    const m = parseInt(document.getElementById('numConstraints').value) || <?= $m ?>;
    
    const cData = <?= json_encode($c) ?>;
    const aData = <?= json_encode($A) ?>;
    const bData = <?= json_encode($b) ?>;
    const typesData = <?= json_encode($constraints_types) ?>;
    const intVarsData = <?= json_encode($integer_vars) ?>;

    let html = '<div class="mb-3"><label class="form-label small mb-1">ضرایب تابع هدف (c)</label><div class="d-flex gap-2 flex-wrap">';
    for (let j = 0; j < n; j++) {
        const isChecked = intVarsData.includes(j) ? 'checked' : '';
        html += `<div class="flex-fill">
            <small class="d-block text-muted">x${j+1}</small>
            <input type="number" step="any" class="form-control form-control-sm p-1 c-input" data-j="${j}" value="${cData[j] || 0}">
            <div class="form-check mt-1">
                <input type="checkbox" class="form-check-input int-var-check" data-j="${j}" id="intVar${j}" ${isChecked}>
                <label class="form-check-label" for="intVar${j}" style="font-size:.7rem;">صحیح</label>
            </div>
        </div>`;
    }
    html += '</div></div>';

    html += '<label class="form-label small mb-1">محدودیت‌ها</label>';
    for (let i = 0; i < m; i++) {
        html += `<div class="input-group input-group-sm mb-2">`;
        for (let j = 0; j < n; j++) {
            html += `<input type="number" step="any" class="form-control a-input" data-i="${i}" data-j="${j}" value="${aData[i]?.[j] || 0}">`;
        }
        html += `<select class="form-select constraint-type" data-i="${i}" style="max-width: 60px;">
                    <option value="<=" ${typesData[i] === '<=' ? 'selected' : ''}>≤</option>
                    <option value=">=" ${typesData[i] === '>=' ? 'selected' : ''}>≥</option>
                    <option value="=" ${typesData[i] === '=' ? 'selected' : ''}>=</option>
                 </select>`;
        html += `<input type="number" step="any" class="form-control b-input" data-i="${i}" value="${bData[i] || 0}">`;
        html += `</div>`;
    }

    document.getElementById('dynamicForm').innerHTML = html;
}

function getProblemData() {
    const n = parseInt(document.getElementById('numVars').value) || <?= $n ?>;
    const m = parseInt(document.getElementById('numConstraints').value) || <?= $m ?>;

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

async function updateILP(projectId) {
    const data = getProblemData();
    if (data.integer_vars.length === 0) {
        alert('⚠️ حداقل یک متغیر باید عدد صحیح باشد.');
        return;
    }

    const payload = {
        ...data,
        save: true,
        project_id: projectId,
        name: '<?= addslashes($project['name']) ?>'
    };

    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>در حال حل...';

    try {
        const res = await fetch('<?= or_url('controller=ilp&action=solve') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });
        const result = await res.json();
        if (!result.success) {
            alert('❌ ' + escapeHtml(result.error));
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> ذخیره تغییرات و حل مجدد';
            return;
        }
        alert('✅ پروژه با موفقیت به‌روزرسانی شد!');
        window.location.href = '<?= or_url('controller=ilp&action=show&id=' . (int)$project['id']) ?>';
    } catch (e) {
        alert('❌ خطای شبکه: ' + escapeHtml(e.message));
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> ذخیره تغییرات و حل مجدد';
    }
}

document.addEventListener('DOMContentLoaded', () => { buildEditForm(); });
</script>