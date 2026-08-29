<?php ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-project-diagram text-primary"></i> ایجاد پروژه ترانشیپمنت</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transship') ?>">ترانشیپمنت</a></li>
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
                        <input type="text" id="projName" class="form-control" value="شبکه ترانشیپمنت" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="projDesc" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مبادی (عرضه)</label>
                        <input type="number" id="numSources" class="form-control" value="2" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مقاصد (تقاضا)</label>
                        <input type="number" id="numDestinations" class="form-control" value="2" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد گره‌های میانی (ترانشیپمنت)</label>
                        <input type="number" id="numIntermediates" class="form-control" value="1" min="0" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateTransshipMatrix()">
                        <i class="fas fa-sync-alt"></i> تولید ماتریس
                    </button>
                </div>
            </div>
            <div class="alert alert-info small">
                <i class="fas fa-info-circle"></i> <strong>نکته:</strong> در مسئله ترانشیپمنت، کالا می‌تواند از گره‌های میانی عبور کند.
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-table text-success"></i> ماتریس هزینه</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="transshipMatrix"></table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=transship') ?>" class="btn btn-outline-secondary">بازگشت</a>
                        <button type="button" class="btn btn-or-success" onclick="saveTransshipProject()">
                            <i class="fas fa-save"></i> ذخیره پروژه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let nodeNames = [];

function generateTransshipMatrix() {
    const nS = parseInt(document.getElementById('numSources').value) || 2;
    const nD = parseInt(document.getElementById('numDestinations').value) || 2;
    const nI = parseInt(document.getElementById('numIntermediates').value) || 0;
    const totalNodes = nS + nD + nI;

    nodeNames = [];
    for (let i = 0; i < nS; i++) nodeNames.push({ name: `مبدأ ${i+1}`, type: 'source' });
    for (let j = 0; j < nD; j++) nodeNames.push({ name: `مقصد ${j+1}`, type: 'destination' });
    for (let k = 0; k < nI; k++) nodeNames.push({ name: `میانی ${k+1}`, type: 'intermediate' });

    let html = '<thead><tr><th>گره \\ گره</th>';
    for (let j = 0; j < totalNodes; j++) {
        html += `<th>${nodeNames[j].name}</th>`;
    }
    html += '<th class="supply-demand-cell">ظرفیت</th></tr></thead><tbody>';

    for (let i = 0; i < totalNodes; i++) {
        html += `<tr><th class="supply-demand-cell">${nodeNames[i].name}</th>`;
        for (let j = 0; j < totalNodes; j++) {
            const disabled = (i === j) ? 'disabled value="0"' : '';
            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell" data-i="${i}" data-j="${j}" ${disabled}></td>`;
        }
        const cap = (nodeNames[i].type === 'intermediate') ? 0 : 0;
        html += `<td><input type="number" class="form-control form-control-sm capacity-cell" data-i="${i}" value="${cap}"></td></tr>`;
    }
    html += '</tbody>';
    document.getElementById('transshipMatrix').innerHTML = html;
}

async function saveTransshipProject() {
    const nS = parseInt(document.getElementById('numSources').value);
    const nD = parseInt(document.getElementById('numDestinations').value);
    const nI = parseInt(document.getElementById('numIntermediates').value);

    const sources = [], destinations = [], intermediates = [];
    for (let i = 0; i < nS; i++) sources.push({ name: `مبدأ ${i+1}`, capacity: 0 });
    for (let j = 0; j < nD; j++) destinations.push({ name: `مقصد ${j+1}`, capacity: 0 });
    for (let k = 0; k < nI; k++) intermediates.push({ name: `میانی ${k+1}`, capacity: 0 });

    // خواندن ظرفیت‌ها
    document.querySelectorAll('.capacity-cell').forEach(el => {
        const i = parseInt(el.dataset.i);
        const val = parseInt(el.value) || 0;
        if (i < nS) sources[i].capacity = val;
        else if (i < nS + nD) destinations[i - nS].capacity = val;
        else intermediates[i - nS - nD].capacity = val;
    });

    // خواندن ماتریس هزینه
    const totalNodes = nS + nD + nI;
    const costMatrix = [];
    for (let i = 0; i < totalNodes; i++) {
        const row = [];
        for (let j = 0; j < totalNodes; j++) {
            const cell = document.querySelector(`.cost-cell[data-i="${i}"][data-j="${j}"]`);
            row.push(cell ? (cell.value === '' ? null : parseFloat(cell.value)) : 0);
        }
        costMatrix.push(row);
    }

    const payload = {
        name: document.getElementById('projName').value,
        description: document.getElementById('projDesc').value,
        sources, destinations, intermediates,
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=transship&action=store") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ پروژه ذخیره شد!');
            window.location.href = '<?= or_url("controller=transship") ?>';
        } else {
            alert('❌ ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه');
    } finally {
        btn.innerHTML = '<i class="fas fa-save"></i> ذخیره پروژه';
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', generateTransshipMatrix);
</script>