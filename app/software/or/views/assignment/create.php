<?php
/**
 * فرم ایجاد پروژه تخصیص
 * مسیر: app/software/or/views/assignment/create.php
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-users-cog text-primary"></i> ایجاد پروژه تخصیص</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=assignment') ?>">تخصیص</a></li>
                    <li class="breadcrumb-item active">ایجاد جدید</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-cog text-primary"></i> تنظیمات مدل</h5>
                    <div class="mb-3">
                        <label class="form-label">نام پروژه</label>
                        <input type="text" id="projName" class="form-control" value="تخصیص بهینه نیروی کار" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="projDesc" class="form-control" rows="2" placeholder="توضیحات مختصر..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">هدف مسئله</label>
                        <select id="objective" class="form-select">
                            <option value="minimize">کمینه‌سازی (هزینه/زمان)</option>
                            <option value="maximize">بیشینه‌سازی (سود/کارایی)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد عوامل (Agents)</label>
                        <input type="number" id="numAgents" class="form-control" value="3" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد وظایف (Tasks)</label>
                        <input type="number" id="numTasks" class="form-control" value="3" min="1" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateAssignmentMatrix()">
                        <i class="fas fa-sync-alt"></i> تولید ماتریس
                    </button>
                </div>
            </div>
            <div class="alert alert-info small">
                <i class="fas fa-info-circle"></i> <strong>نکته:</strong> اگر تعداد عوامل و وظایف برابر نباشد، سیستم به صورت خودکار یک سطر یا ستون مجازی (Dummy) با هزینه صفر اضافه خواهد کرد.
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-table text-success"></i> ماتریس هزینه/سود</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="assignmentMatrix">
                            <!-- توسط جاوااسکریپت پر می‌شود -->
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=assignment') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="saveAssignmentProject()">
                            <i class="fas fa-save"></i> ذخیره پروژه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateAssignmentMatrix() {
    const numA = parseInt(document.getElementById('numAgents').value) || 3;
    const numT = parseInt(document.getElementById('numTasks').value) || 3;

    let html = '<thead><tr><th>عامل \\ وظیفه</th>';
    for (let j = 1; j <= numT; j++) {
        html += `<th>وظیفه ${j}</th>`;
    }
    html += '</tr></thead><tbody>';

    for (let i = 1; i <= numA; i++) {
        html += `<tr><th class="supply-demand-cell">عامل ${i}</th>`;
        for (let j = 1; j <= numT; j++) {
            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell" data-i="${i-1}" data-j="${j-1}" placeholder="0"></td>`;
        }
        html += '</tr>';
    }
    html += '</tbody>';

    document.getElementById('assignmentMatrix').innerHTML = html;
}

async function saveAssignmentProject() {
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
        agents: agents,
        tasks: tasks,
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=assignment&action=store") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ پروژه تخصیص با موفقیت ذخیره شد!');
            window.location.href = '<?= or_url("controller=assignment") ?>';
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه: ' + e.message);
    } finally {
        btn.innerHTML = '<i class="fas fa-save"></i> ذخیره پروژه';
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', generateAssignmentMatrix);
</script>