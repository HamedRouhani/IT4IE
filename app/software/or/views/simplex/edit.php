<?php
/**
 * فرم ویرایش مدل برنامه‌ریزی خطی
 * مسیر: app/software/or/views/simplex/edit.php
 */

$modelData = json_decode($project['model_data'] ?? '{}', true) ?: [];

$c = $modelData['c'] ?? [];
$A = $modelData['A'] ?? [];
$b = $modelData['b'] ?? [];
$types = $modelData['types'] ?? [];

$numVars = count($c);
$numConstraints = count($b);
$objective = $project['objective'] ?? 'maximize';

$cJson = json_encode($c, JSON_UNESCAPED_UNICODE) ?: '[]';
$AJson = json_encode($A, JSON_UNESCAPED_UNICODE) ?: '[]';
$bJson = json_encode($b, JSON_UNESCAPED_UNICODE) ?: '[]';
$typesJson = json_encode($types, JSON_UNESCAPED_UNICODE) ?: '[]';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit text-warning"></i> ویرایش مدل برنامه‌ریزی خطی</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=simplex') ?>">برنامه‌ریزی خطی</a></li>
                    <li class="breadcrumb-item active">ویرایش <?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- تنظیمات اولیه -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-cog text-primary"></i> تنظیمات مدل</h5>
                    <div class="mb-3">
                        <label class="form-label">نام مدل</label>
                        <input type="text" id="modelName" class="form-control" value="<?= or_e($project['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="modelDesc" class="form-control" rows="2"><?= or_e($project['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">هدف مسئله</label>
                        <select id="objective" class="form-select">
                            <option value="maximize" <?= $objective === 'maximize' ? 'selected' : '' ?>>بیشینه‌سازی (Maximize)</option>
                            <option value="minimize" <?= $objective === 'minimize' ? 'selected' : '' ?>>کمینه‌سازی (Minimize)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label">تعداد متغیرها</label>
                            <input type="number" id="numVars" class="form-control" value="<?= max(1, $numVars) ?>" min="1" max="20">
                        </div>
                        <div class="col-6">
                            <label class="form-label">تعداد محدودیت‌ها</label>
                            <input type="number" id="numConstraints" class="form-control" value="<?= max(1, $numConstraints) ?>" min="1" max="20">
                        </div>
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateSimplexMatrix(true)">
                        <i class="fas fa-sync-alt"></i> بازسازی ماتریس با داده‌های فعلی
                    </button>
                    <small class="text-danger d-block mt-2 text-center">⚠️ تغییر اعداد بالا و زدن دکمه، مقادیر واردشده را بازنشانی می‌کند.</small>
                </div>
            </div>
        </div>

        <!-- ماتریس ورودی -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-table text-success"></i> ماتریس ضرایب</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="simplexMatrix"></table>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap gap-2">
                        <a href="<?= or_url('controller=simplex&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت
                        </a>
                        <div class="d-flex gap-2">
                            <!-- ✅ دکمه ذخیره ساده -->
                            <button type="button" id="saveBtn" class="btn btn-or-warning" onclick="updateSimplexProject(<?= $project['id'] ?>, false)">
                                <i class="fas fa-save"></i> ذخیره تغییرات
                            </button>
                            <!-- ✅ دکمه ذخیره + حل -->
                            <button type="button" id="saveAndSolveBtn" class="btn btn-or-success" onclick="updateSimplexProject(<?= $project['id'] ?>, true)">
                                <i class="fas fa-play"></i> ذخیره و حل مسئله
                            </button>
                        </div>
                    </div>
                    <div id="solveLoader" class="d-none mt-3 text-center">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2 mb-0">در حال پردازش...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const initialC = <?= $cJson ?>;
const initialA = <?= $AJson ?>;
const initialB = <?= $bJson ?>;
const initialTypes = <?= $typesJson ?>;

function generateSimplexMatrix(isEdit = false) {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;
    const objType = document.getElementById('objective').value;
    const objLabel = objType === 'maximize' ? 'Max Z =' : 'Min Z =';
    
    let html = '<thead class="table-light"><tr><th>متغیر</th>';
    for (let j = 1; j <= n; j++) html += `<th>x<sub>${j}</sub></th>`;
    html += '<th>عملگر</th><th>سمت راست (RHS)</th></tr></thead><tbody>';
    
    html += `<tr class="table-primary"><td class="fw-bold">${objLabel}</td>`;
    for (let j = 1; j <= n; j++) {
        html += `<td><input type="number" step="any" class="form-control form-control-sm obj-c" data-j="${j-1}" value="0"></td>`;
    }
    html += '<td>-</td><td>-</td></tr>';
    
    for (let i = 1; i <= m; i++) {
        html += `<tr><td class="fw-bold">محدودیت ${i}</td>`;
        for (let j = 1; j <= n; j++) {
            html += `<td><input type="number" step="any" class="form-control form-control-sm matrix-a" data-i="${i-1}" data-j="${j-1}" value="0"></td>`;
        }
        html += `<td>
            <select class="form-select form-select-sm constraint-type" data-i="${i-1}">
                <option value="<=">&le;</option>
                <option value=">=">&ge;</option>
                <option value="=">=</option>
            </select>
        </td>`;
        html += `<td><input type="number" step="any" class="form-control form-control-sm vector-b" data-i="${i-1}" value="0"></td></tr>`;
    }
    html += '</tbody>';
    
    document.getElementById('simplexMatrix').innerHTML = html;
    if (isEdit) populateData(n, m);
}

function populateData(n, m) {
    document.querySelectorAll('.obj-c').forEach(input => {
        const j = parseInt(input.dataset.j);
        if (initialC[j] !== undefined) input.value = initialC[j];
    });
    document.querySelectorAll('.matrix-a').forEach(input => {
        const i = parseInt(input.dataset.i);
        const j = parseInt(input.dataset.j);
        if (initialA[i] && initialA[i][j] !== undefined) input.value = initialA[i][j];
    });
    document.querySelectorAll('.vector-b').forEach(input => {
        const i = parseInt(input.dataset.i);
        if (initialB[i] !== undefined) input.value = initialB[i];
    });
    document.querySelectorAll('.constraint-type').forEach(select => {
        const i = parseInt(select.dataset.i);
        if (initialTypes[i] !== undefined) select.value = initialTypes[i];
    });
}

function collectData() {
    const n = parseInt(document.getElementById('numVars').value) || 2;
    const m = parseInt(document.getElementById('numConstraints').value) || 2;
    
    const c = [];
    document.querySelectorAll('.obj-c').forEach(input => {
        c[parseInt(input.dataset.j)] = parseFloat(input.value) || 0;
    });
    
    const A = [];
    document.querySelectorAll('.matrix-a').forEach(input => {
        const i = parseInt(input.dataset.i);
        const j = parseInt(input.dataset.j);
        if (!A[i]) A[i] = [];
        A[i][j] = parseFloat(input.value) || 0;
    });
    
    const b = [];
    document.querySelectorAll('.vector-b').forEach(input => {
        b[parseInt(input.dataset.i)] = parseFloat(input.value) || 0;
    });
    
    const types = [];
    document.querySelectorAll('.constraint-type').forEach(select => {
        types[parseInt(select.dataset.i)] = select.value;
    });

    return {
        name: document.getElementById('modelName').value,
        description: document.getElementById('modelDesc').value,
        objective: document.getElementById('objective').value,
        c, A, b, types
    };
}

async function updateSimplexProject(id, shouldSolve) {
    const saveBtn = document.getElementById('saveBtn');
    const solveBtn = document.getElementById('saveAndSolveBtn');
    const loader = document.getElementById('solveLoader');
    
    // غیرفعال کردن هر دو دکمه
    saveBtn.disabled = true;
    solveBtn.disabled = true;
    loader.classList.remove('d-none');
    
    const originalSaveText = saveBtn.innerHTML;
    const originalSolveText = solveBtn.innerHTML;
    
    if (shouldSolve) {
        solveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال حل...';
    } else {
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    }

    const payload = { ...collectData(), solve_after_update: shouldSolve };

    try {
        const res = await fetch('<?= or_url("controller=simplex&action=update&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        
        if (data.success) {
            if (data.solved) {
                alert('✅ تغییرات ذخیره و مسئله با موفقیت حل شد!\nمقدار بهینه: ' + (data.result?.optimal_value ?? 'نامشخص'));
            } else if (data.error) {
                alert('⚠️ تغییرات ذخیره شد اما مسئله حل نشد:\n' + data.error);
            } else {
                alert('✅ تغییرات با موفقیت ذخیره شد.');
            }
            window.location.href = data.redirect || '<?= or_url("controller=simplex&action=show&id=") ?>' + id;
        } else {
            alert('❌ خطا: ' + (data.error || 'خطای نامشخص'));
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        saveBtn.innerHTML = originalSaveText;
        solveBtn.innerHTML = originalSolveText;
        saveBtn.disabled = false;
        solveBtn.disabled = false;
        loader.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    generateSimplexMatrix(true);
});
</script>