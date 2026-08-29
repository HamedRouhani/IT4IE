<?php
/**
 * فرم ایجاد پروژه حمل و نقل
 * مسیر: app/software/or/views/transport/create.php
 */
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-truck text-primary"></i> ایجاد پروژه حمل و نقل</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('') ?>">OR Analyzer</a></li>
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transport') ?>">حمل و نقل</a></li>
                    <li class="breadcrumb-item active">ایجاد جدید</li>
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
                        <label class="form-label">نام پروژه</label>
                        <input type="text" id="projName" class="form-control" value="بهینه‌سازی حمل و نقل" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مبادی (عرضه)</label>
                        <input type="number" id="numSources" class="form-control" value="2" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مقاصد (تقاضا)</label>
                        <input type="number" id="numDestinations" class="form-control" value="2" min="1" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateTransportMatrix()">
                        <i class="fas fa-sync-alt"></i> تولید ماتریس
                    </button>
                </div>
            </div>
            
            <!-- کارت وضعیت توازن -->
            <div class="card border-0 shadow-sm" id="balanceCard" style="display: none;">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">وضعیت توازن</h6>
                    <div id="balanceStatus" class="h4 mb-0"></div>
                    <small class="text-muted d-block mt-2">عرضه کل: <span id="totalSupply">0</span> | تقاضای کل: <span id="totalDemand">0</span></small>
                </div>
            </div>
        </div>

        <!-- ماتریس ورودی -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-table text-success"></i> ماتریس هزینه و ظرفیت‌ها</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="transportMatrix">
                            <!-- توسط جاوااسکریپت پر می‌شود -->
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=transport') ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="saveTransportProject()">
                            <i class="fas fa-save"></i> ذخیره پروژه
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateTransportMatrix() {
    const numS = parseInt(document.getElementById('numSources').value) || 2;
    const numD = parseInt(document.getElementById('numDestinations').value) || 2;

    let html = '<thead><tr><th>مبدأ \\ مقصد</th>';
    for (let j = 1; j <= numD; j++) {
        html += `<th>مقصد ${j}</th>`;
    }
    html += '<th class="supply-demand-cell">ظرفیت عرضه</th></tr></thead><tbody>';

    for (let i = 1; i <= numS; i++) {
        html += `<tr><th class="supply-demand-cell">مبدأ ${i}</th>`;
        for (let j = 1; j <= numD; j++) {
            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell" data-i="${i-1}" data-j="${j-1}" placeholder="0" oninput="checkBalance()"></td>`;
        }
        html += `<td><input type="number" class="form-control form-control-sm supply-cell" data-i="${i-1}" value="0" oninput="checkBalance()"></td></tr>`;
    }

    // سطر تقاضا
    html += '<tr><th class="supply-demand-cell">تقاضای مقصد</th>';
    for (let j = 1; j <= numD; j++) {
        html += `<td><input type="number" class="form-control form-control-sm demand-cell" data-j="${j-1}" value="0" oninput="checkBalance()"></td>`;
    }
    html += '<td class="supply-demand-cell">-</td></tr></tbody>';

    document.getElementById('transportMatrix').innerHTML = html;
    document.getElementById('balanceCard').style.display = 'block';
    checkBalance();
}

function checkBalance() {
    let supply = 0, demand = 0;
    document.querySelectorAll('.supply-cell').forEach(el => supply += parseInt(el.value) || 0);
    document.querySelectorAll('.demand-cell').forEach(el => demand += parseInt(el.value) || 0);

    document.getElementById('totalSupply').innerText = supply;
    document.getElementById('totalDemand').innerText = demand;

    const statusEl = document.getElementById('balanceStatus');
    if (supply === demand) {
        statusEl.innerHTML = '<span class="badge bg-success fs-6">✅ متوازن</span>';
    } else if (supply > demand) {
        statusEl.innerHTML = '<span class="badge bg-warning text-dark fs-6">⚠️ عرضه > تقاضا</span>';
    } else {
        statusEl.innerHTML = '<span class="badge bg-danger fs-6">⚠️ تقاضا > عرضه</span>';
    }
}

async function saveTransportProject() {
    const numS = parseInt(document.getElementById('numSources').value);
    const numD = parseInt(document.getElementById('numDestinations').value);
    
    const sources = [];
    document.querySelectorAll('.supply-cell').forEach((el, i) => {
        sources.push({ name: `مبدأ ${i+1}`, capacity: parseInt(el.value) || 0 });
    });

    const destinations = [];
    document.querySelectorAll('.demand-cell').forEach((el, j) => {
        destinations.push({ name: `مقصد ${j+1}`, capacity: parseInt(el.value) || 0 });
    });

    const costMatrix = [];
    for (let i = 0; i < numS; i++) {
        const row = [];
        for (let j = 0; j < numD; j++) {
            const cell = document.querySelector(`.cost-cell[data-i="${i}"][data-j="${j}"]`);
            row.push(cell.value === '' ? null : parseFloat(cell.value));
        }
        costMatrix.push(row);
    }

    const payload = {
        name: document.getElementById('projName').value,
        sources: sources,
        destinations: destinations,
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=transport&action=store") ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ پروژه با موفقیت ذخیره شد!');
            window.location.href = '<?= or_url("controller=transport") ?>';
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (e) {
        console.error("Full Error:", e);
        alert('❌ خطا در ذخیره: ' + e.message + '\n\nلطفاً کنسول مرورگر (F12) را برای جزئیات بیشتر بررسی کنید.');
    } finally {
        btn.innerHTML = '<i class="fas fa-save"></i> ذخیره پروژه';
        btn.disabled = false;
    }
}

document.addEventListener('DOMContentLoaded', generateTransportMatrix);
</script>