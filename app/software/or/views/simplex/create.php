<?php
/**
 * فرم ایجاد مدل برنامه‌ریزی خطی (Simplex)
 * مسیر: app/software/or/views/simplex/create.php
 */
?>

<div class="container-fluid py-4">
    <!-- هدر صفحه -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-chart-line text-primary"></i> ایجاد مدل برنامه‌ریزی خطی
            </h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=simplex') ?>">برنامه‌ریزی خطی</a></li>
                    <li class="breadcrumb-item active">ایجاد مدل جدید</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- ستون تنظیمات -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-cog text-primary"></i> تنظیمات مدل
                    </h5>
                    
                    <form id="simplexForm">
                        <!-- نام مدل -->
                        <div class="mb-3">
                            <label for="modelName" class="form-label">نام مدل</label>
                            <input type="text" class="form-control" id="modelName" 
                                   value="مدل تولید بهینه" required>
                        </div>

                        <!-- نوع تابع هدف -->
                        <div class="mb-3">
                            <label class="form-label">هدف مسئله</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="form-check card border p-2">
                                        <input class="form-check-input" type="radio" 
                                               name="objType" id="objMax" value="maximize" checked>
                                        <label class="form-check-label w-100" for="objMax">
                                            <strong>بیشینه‌سازی</strong>
                                            <br><small class="text-muted">Max Z</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-check card border p-2">
                                        <input class="form-check-input" type="radio" 
                                               name="objType" id="objMin" value="minimize">
                                        <label class="form-check-label w-100" for="objMin">
                                            <strong>کمینه‌سازی</strong>
                                            <br><small class="text-muted">Min Z</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- تعداد متغیرها و محدودیت‌ها -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="numVars" class="form-label">تعداد متغیرها</label>
                                <input type="number" class="form-control" id="numVars" 
                                       value="2" min="1" max="20" required>
                            </div>
                            <div class="col-6">
                                <label for="numConstraints" class="form-label">تعداد محدودیت‌ها</label>
                                <input type="number" class="form-control" id="numConstraints" 
                                       value="2" min="1" max="20" required>
                            </div>
                        </div>

                        <button type="button" class="btn btn-or-primary w-100" onclick="generateMatrix()">
                            <i class="fas fa-sync-alt"></i> تولید ماتریس
                        </button>
                    </form>
                </div>
            </div>

            <!-- راهنما -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-2">
                        <i class="fas fa-info-circle text-info"></i> راهنما
                    </h6>
                    <small class="text-muted">
                        <ul class="mb-0 ps-3">
                            <li>متغیرها: x₁, x₂, x₃, ...</li>
                            <li>ضرایب تابع هدف را در سطر اول وارد کنید</li>
                            <li>ضرایب محدودیت‌ها را در سطرهای بعدی وارد کنید</li>
                            <li>نوع محدودیت: ≤, ≥, =</li>
                            <li>سمت راست (RHS) را در ستون آخر وارد کنید</li>
                        </ul>
                    </small>
                </div>
            </div>
        </div>

        <!-- ستون ماتریس -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-table text-success"></i> ماتریس ضرایب
                    </h5>
                    
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="simplexMatrix">
                            <!-- ماتریس به صورت داینامیک اینجا تولید می‌شود -->
                        </table>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=simplex') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="submitModel()">
                            <i class="fas fa-play"></i> ذخیره و حل مدل
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// تولید داینامیک ماتریس
function generateMatrix() {
    const numVars = parseInt(document.getElementById('numVars').value) || 2;
    const numConstraints = parseInt(document.getElementById('numConstraints').value) || 2;
    const objType = document.querySelector('input[name="objType"]:checked').value;
    const objLabel = objType === 'maximize' ? 'Max Z =' : 'Min Z =';

    let html = '<thead><tr>';
    html += '<th>متغیر</th>';
    for (let j = 1; j <= numVars; j++) {
        html += `<th>x<sub>${j}</sub></th>`;
    }
    html += '<th>عملگر</th>';
    html += '<th>سمت راست</th>';
    html += '</tr></thead><tbody>';

    // سطر تابع هدف
    html += `<tr class="table-primary">`;
    html += `<td class="fw-bold">${objLabel}</td>`;
    for (let j = 1; j <= numVars; j++) {
        html += `<td>
            <input type="number" step="any" class="form-control form-control-sm obj-c" 
                   data-j="${j-1}" value="0">
        </td>`;
    }
    html += '<td>-</td>';
    html += '<td>-</td>';
    html += '</tr>';

    // سطرهای محدودیت
    for (let i = 1; i <= numConstraints; i++) {
        html += `<tr>`;
        html += `<td class="fw-bold">محدودیت ${i}</td>`;
        for (let j = 1; j <= numVars; j++) {
            html += `<td>
                <input type="number" step="any" class="form-control form-control-sm matrix-a" 
                       data-i="${i-1}" data-j="${j-1}" value="0">
            </td>`;
        }
        html += `<td>
            <select class="form-select form-select-sm constraint-type" data-i="${i-1}">
                <option value="<=">≤</option>
                <option value=">=">≥</option>
                <option value="=">=</option>
            </select>
        </td>`;
        html += `<td>
            <input type="number" step="any" class="form-control form-control-sm vector-b" 
                   data-i="${i-1}" value="0">
        </td>`;
        html += '</tr>';
    }
    html += '</tbody>';

    document.getElementById('simplexMatrix').innerHTML = html;
}

// ارسال مدل
async function submitModel() {
    const numVars = parseInt(document.getElementById('numVars').value);
    const numConstraints = parseInt(document.getElementById('numConstraints').value);
    const modelName = document.getElementById('modelName').value;
    const objType = document.querySelector('input[name="objType"]:checked').value;

    // جمع‌آوری ضرایب تابع هدف
    const c = [];
    document.querySelectorAll('.obj-c').forEach(input => {
        c[input.dataset.j] = parseFloat(input.value) || 0;
    });

    // جمع‌آوری ضرایب محدودیت‌ها
    const A = [];
    document.querySelectorAll('.matrix-a').forEach(input => {
        const i = parseInt(input.dataset.i);
        const j = parseInt(input.dataset.j);
        if (!A[i]) A[i] = [];
        A[i][j] = parseFloat(input.value) || 0;
    });

    // جمع‌آوری سمت راست
    const b = [];
    document.querySelectorAll('.vector-b').forEach(input => {
        b[input.dataset.i] = parseFloat(input.value) || 0;
    });

    // جمع‌آوری نوع محدودیت‌ها
    const types = [];
    document.querySelectorAll('.constraint-type').forEach(select => {
        types[select.dataset.i] = select.value;
    });

    const payload = {
        name: modelName,
        obj_type: objType,
        c: c,
        A: A,
        b: b,
        types: types
    };

    // نمایش لودر
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال حل...';
    btn.disabled = true;

    try {
        // ذخیره مدل
        const saveRes = await fetch('<?= or_url("controller=simplex&action=store") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const saveData = await saveRes.json();

        if (!saveData.success) {
            alert('خطا در ذخیره: ' + (saveData.error || 'خطای ناشناخته'));
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        // حل مدل
        const solveRes = await fetch(`<?= or_url("controller=simplex&action=solve&id=") ?>${saveData.project_id}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        });
        const solveData = await solveRes.json();

        if (solveData.success) {
            if (solveData.result.status === 'optimal') {
                // انتقال به صفحه نتایج
                window.location.href = `<?= or_url("controller=simplex&action=result&id=") ?>${saveData.project_id}`;
            } else {
                alert(`وضعیت: ${solveData.result.status}\nپیام: ${solveData.result.message}`);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        } else {
            alert('خطا در حل: ' + (solveData.error || 'خطای ناشناخته'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    } catch (error) {
        alert('خطای شبکه: ' + error.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// تولید اولیه ماتریس هنگام بارگذاری صفحه
document.addEventListener('DOMContentLoaded', generateMatrix);
</script>