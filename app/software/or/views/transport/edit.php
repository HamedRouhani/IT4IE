<?php
/**
 * فرم ویرایش پروژه حمل و نقل
 * مسیر: app/software/or/views/transport/edit.php
 * نسخه اصلاح‌شده: بازسازی ماتریس هزینه + فرمت هوشمند اعداد
 */

// ─────────────────────────────────────────────
// تابع فرمت‌بندی هوشمند اعداد
// ──────────────────────────────────────────────
if (!function_exists('orFormatNumber')) {
    function orFormatNumber($value, $maxDecimals = 4) {
        if ($value === null || $value === '' || $value === '-') return null;
        $float = (float)$value;
        // اگر عدد صحیح است، بدون اعشار برگردان
        if ($float == floor($float)) {
            return (int)$float;
        }
        // در غیر این صورت با حذف صفرهای اضافی
        $formatted = number_format($float, $maxDecimals, '.', '');
        $formatted = rtrim($formatted, '0');
        $formatted = rtrim($formatted, '.');
        return $formatted;
    }
}

// بازسازی ماتریس هزینه از روی داده‌های edges
$costMatrix = [];
$numS = count($sources);
$numD = count($destinations);

// مقداردهی اولیه ماتریس با null
for ($i = 0; $i < $numS; $i++) {
    $costMatrix[$i] = array_fill(0, $numD, null);
}

// پر کردن ماتریس با مقادیر هزینه از آرایه edges
foreach ($edges as $edge) {
    $rowIndex = -1;
    foreach ($sources as $idx => $src) {
        if ($src['id'] == $edge['source_id']) {
            $rowIndex = $idx;
            break;
        }
    }
    
    $colIndex = -1;
    foreach ($destinations as $idx => $dst) {
        if ($dst['id'] == $edge['destination_id']) {
            $colIndex = $idx;
            break;
        }
    }
    
    if ($rowIndex !== -1 && $colIndex !== -1) {
        // اعمال فرمت هوشمند روی هزینه
        $costMatrix[$rowIndex][$colIndex] = $edge['is_prohibited'] ? null : orFormatNumber($edge['cost']);
    }
}

// اعمال فرمت هوشمند روی ظرفیت مبادی و مقاصد
$formattedSources = [];
foreach ($sources as $src) {
    $formattedSources[] = [
        'id' => $src['id'],
        'name' => $src['name'],
        'capacity' => orFormatNumber($src['capacity']),
        'sort_order' => $src['sort_order'] ?? 0
    ];
}

$formattedDestinations = [];
foreach ($destinations as $dst) {
    $formattedDestinations[] = [
        'id' => $dst['id'],
        'name' => $dst['name'],
        'capacity' => orFormatNumber($dst['capacity']),
        'sort_order' => $dst['sort_order'] ?? 0
    ];
}

$sourcesJson = json_encode($formattedSources);
$destinationsJson = json_encode($formattedDestinations);
$costMatrixJson = json_encode($costMatrix);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit text-warning"></i> ویرایش پروژه حمل و نقل</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transport') ?>">حمل و نقل</a></li>
                    <li class="breadcrumb-item active">ویرایش <?= or_e($project['name']) ?></li>
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
                        <input type="text" id="projName" class="form-control" value="<?= or_e($project['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="projDesc" class="form-control" rows="2"><?= or_e($project['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مبادی (عرضه)</label>
                        <input type="number" id="numSources" class="form-control" value="<?= $numS ?>" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مقاصد (تقاضا)</label>
                        <input type="number" id="numDestinations" class="form-control" value="<?= $numD ?>" min="1" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateTransportMatrix(true)">
                        <i class="fas fa-sync-alt"></i> بازسازی ماتریس با ابعاد جدید
                    </button>
                    <small class="text-danger d-block mt-2 text-center">⚠️ تغییر تعداد مبادی/مقاصد، داده‌های ماتریس را بازنشانی می‌کند.</small>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm" id="balanceCard">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">وضعیت توازن فعلی</h6>
                    <div id="balanceStatus" class="h4 mb-0"></div>
                    <small class="text-muted d-block mt-2">عرضه کل: <span id="totalSupply">0</span> | تقاضای کل: <span id="totalDemand">0</span></small>
                </div>
            </div>
        </div>

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
                        <a href="<?= or_url('controller=transport&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="updateTransportProject(<?= $project['id'] ?>)">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// داده‌های اولیه از دیتابیس (با فرمت هوشمند)
const initialSources = <?= $sourcesJson ?>;
const initialDestinations = <?= $destinationsJson ?>;
const initialCostMatrix = <?= $costMatrixJson ?>;

function generateTransportMatrix(isEdit = false) {
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
            let val = '';
            // اگر در حالت ویرایش هستیم و داده اولیه برای این خانه وجود دارد، آن را قرار بده
            if (isEdit && initialCostMatrix && initialCostMatrix[i-1] && initialCostMatrix[i-1][j-1] !== undefined && initialCostMatrix[i-1][j-1] !== null) {
                val = initialCostMatrix[i-1][j-1];
            }
            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell" data-i="${i-1}" data-j="${j-1}" value="${val}" placeholder="0" oninput="checkBalance()"></td>`;
        }
        const sCap = isEdit && initialSources[i-1] ? initialSources[i-1].capacity : 0;
        html += `<td><input type="number" class="form-control form-control-sm supply-cell" data-i="${i-1}" value="${sCap}" oninput="checkBalance()"></td></tr>`;
    }

    html += '<tr><th class="supply-demand-cell">تقاضای مقصد</th>';
    for (let j = 1; j <= numD; j++) {
        const dCap = isEdit && initialDestinations[j-1] ? initialDestinations[j-1].capacity : 0;
        html += `<td><input type="number" class="form-control form-control-sm demand-cell" data-j="${j-1}" value="${dCap}" oninput="checkBalance()"></td>`;
    }
    html += '<td class="supply-demand-cell">-</td></tr></tbody>';

    document.getElementById('transportMatrix').innerHTML = html;
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
        statusEl.innerHTML = '<span class="badge bg-danger fs-6">️ تقاضا > عرضه</span>';
    }
}

async function updateTransportProject(id) {
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
        description: document.getElementById('projDesc').value,
        sources: sources,
        destinations: destinations,
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=transport&action=update&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ تغییرات با موفقیت ذخیره شد!');
            window.location.href = '<?= or_url("controller=transport&action=show&id=") ?>' + id;
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

document.addEventListener('DOMContentLoaded', () => generateTransportMatrix(true));
</script>