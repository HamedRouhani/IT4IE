<?php
/**
 * فرم ویرایش پروژه تخصیص
 * مسیر: app/software/or/views/assignment/edit.php
 */
$sourcesJson = json_encode($sources);
$destinationsJson = json_encode($destinations);
$costMatrixJson = json_encode($costMatrix);
$prohibitedJson = json_encode($prohibited);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit text-warning"></i> ویرایش پروژه تخصیص</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=assignment') ?>">تخصیص</a></li>
                    <li class="breadcrumb-item active">ویرایش <?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">تنظیمات</h5>
                    <div class="mb-3">
                        <label class="form-label">نام پروژه</label>
                        <input type="text" id="projName" class="form-control" value="<?= or_e($project['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="projDesc" class="form-control" rows="2"><?= or_e($project['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">هدف مسئله</label>
                        <select id="objective" class="form-select">
                            <option value="minimize" <?= $project['objective'] === 'minimize' ? 'selected' : '' ?>>کمینه‌سازی</option>
                            <option value="maximize" <?= $project['objective'] === 'maximize' ? 'selected' : '' ?>>بیشینه‌سازی</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد عوامل</label>
                        <input type="number" id="numAgents" class="form-control" value="<?= count($sources) ?>" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد وظایف</label>
                        <input type="number" id="numTasks" class="form-control" value="<?= count($destinations) ?>" min="1" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateAssignmentMatrix(false)">
                        <i class="fas fa-sync-alt"></i> بازسازی ماتریس
                    </button>
                    <small class="text-danger d-block mt-2 text-center">⚠️ بازسازی ماتریس، داده‌های فعلی را پاک می‌کند.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">ماتریس هزینه/سود</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="assignmentMatrix"></table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=assignment&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="updateAssignmentProject(<?= $project['id'] ?>)">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const initialSources = <?= $sourcesJson ?>;
const initialDestinations = <?= $destinationsJson ?>;
const initialCostMatrix = <?= $costMatrixJson ?>;
const initialProhibited = <?= $prohibitedJson ?>;

function generateAssignmentMatrix(isEdit = false) {
    const numA = parseInt(document.getElementById('numAgents').value) || 3;
    const numT = parseInt(document.getElementById('numTasks').value) || 3;

    let html = '<thead><tr><th>عامل \\ وظیفه</th>';
    for (let j = 1; j <= numT; j++) html += `<th>وظیفه ${j}</th>`;
    html += '</tr></thead><tbody>';

    for (let i = 1; i <= numA; i++) {
        html += `<tr><th class="supply-demand-cell">عامل ${i}</th>`;
        for (let j = 1; j <= numT; j++) {
            const rowIdx = i - 1;
            const colIdx = j - 1;
            let val = '';
            let isProhib = false;
            
            // اگر در حالت ویرایش هستیم و داده‌های اولیه وجود دارد، آن‌ها را بارگذاری کن
            if (isEdit && initialCostMatrix && initialCostMatrix[rowIdx] && initialCostMatrix[rowIdx][colIdx] !== undefined) {
                val = initialCostMatrix[rowIdx][colIdx] === null ? '' : initialCostMatrix[rowIdx][colIdx];
                isProhib = initialProhibited[rowIdx][colIdx];
            }

            const placeholder = isProhib ? 'ممنوع' : '0';
            const disabledAttr = isProhib ? 'disabled' : '';
            const bgClass = isProhib ? 'bg-danger bg-opacity-10 text-danger' : '';

            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell ${bgClass}" data-i="${rowIdx}" data-j="${colIdx}" value="${val}" placeholder="${placeholder}" ${disabledAttr}></td>`;
        }
        html += '</tr>';
    }
    html += '</tbody>';

    document.getElementById('assignmentMatrix').innerHTML = html;
}

async function updateAssignmentProject(id) {
    const numA = parseInt(document.getElementById('numAgents').value);
    const numT = parseInt(document.getElementById('numTasks').value);
    
    const agents = [];
    for (let i = 0; i < numA; i++) agents.push({ name: `عامل ${i+1}` });

    const tasks = [];
    for (let j = 0; j < numT; j++) tasks.push({ name: `وظیفه ${j+1}` });

    const costMatrix = [];
    for (let i = 0; i < numA; i++) {
        const row = [];
        for (let j = 0; j < numT; j++) {
            const cell = document.querySelector(`.cost-cell[data-i="${i}"][data-j="${j}"]`);
            row.push(cell.value === '' ? null : parseFloat(cell.value));
        }
        costMatrix.push(row);
    }

    const payload = {
        name: document.getElementById('projName').value,
        description: document.getElementById('projDesc').value,
        objective: document.getElementById('objective').value,
        agents, 
        tasks, 
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=assignment&action=update&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ تغییرات با موفقیت ذخیره شد!');
            window.location.href = '<?= or_url("controller=assignment&action=show&id=") ?>' + id;
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        btn.innerHTML = '<i class="fas fa-save"></i> ذخیره تغییرات';
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', () => generateAssignmentMatrix(true));
</script>