<?php
/**
 * فرم ویرایش پروژه ترانشیپمنت
 * مسیر: app/software/or/views/transship/edit.php
 */

// تابع کمکی برای حذف صفرهای اضافی اعشار
function cleanNumber($val) {
    if ($val === null || $val === '') return '';
    return floatval($val);
}

// آماده‌سازی داده‌ها برای جاوااسکریپت و تمیزسازی اعداد
$sourcesClean = [];
foreach ($sources as $src) {
    $sourcesClean[] = ['name' => $src['name'], 'capacity' => cleanNumber($src['capacity'])];
}

$destinationsClean = [];
foreach ($destinations as $dst) {
    $destinationsClean[] = ['name' => $dst['name'], 'capacity' => cleanNumber($dst['capacity'])];
}

$intermediatesClean = [];
foreach ($intermediates as $mid) {
    $intermediatesClean[] = ['name' => $mid['name'], 'capacity' => cleanNumber($mid['capacity'])];
}

$sourcesJson = json_encode($sourcesClean);
$destinationsJson = json_encode($destinationsClean);
$intermediatesJson = json_encode($intermediatesClean);

// ساخت ماتریس هزینه اولیه از روی داده‌های edges
$allNodes = array_merge($sources, $destinations, $intermediates);
$nodeIndexMap = [];
foreach ($allNodes as $idx => $node) {
    $nodeIndexMap[$node['id']] = $idx;
}

$matrixSize = count($allNodes);
$costMatrix = array_fill(0, $matrixSize, array_fill(0, $matrixSize, ''));

foreach ($edges as $edge) {
    $i = $nodeIndexMap[$edge['source_id']] ?? null;
    $j = $nodeIndexMap[$edge['destination_id']] ?? null;
    if ($i !== null && $j !== null && !$edge['is_prohibited']) {
        // تمیزسازی عدد هزینه
        $costMatrix[$i][$j] = cleanNumber($edge['cost']);
    }
}
$costMatrixJson = json_encode($costMatrix);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit text-warning"></i> ویرایش پروژه ترانشیپمنت</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=transship') ?>">ترانشیپمنت</a></li>
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
                        <input type="number" id="numSources" class="form-control" value="<?= count($sources) ?>" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد مقاصد (تقاضا)</label>
                        <input type="number" id="numDestinations" class="form-control" value="<?= count($destinations) ?>" min="1" max="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد گره‌های میانی</label>
                        <input type="number" id="numIntermediates" class="form-control" value="<?= count($intermediates) ?>" min="0" max="10">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100" onclick="generateTransshipMatrix(true)">
                        <i class="fas fa-sync-alt"></i> بازسازی ماتریس با داده‌های فعلی
                    </button>
                    <small class="text-danger d-block mt-2 text-center">⚠️ تغییر تعداد گره‌ها، داده‌های فعلی را بازنشانی می‌کند.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-table text-success"></i> ماتریس هزینه</h5>
                    <div class="table-responsive mb-3">
                        <table class="or-matrix" id="transshipMatrix">
                            <!-- توسط جاوااسکریپت پر می‌شود -->
                        </table>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=transship&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
                        </a>
                        <button type="button" class="btn btn-or-success" onclick="updateTransshipProject(<?= $project['id'] ?>)">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// دریافت داده‌های اولیه از PHP (که اکنون تمیز شده‌اند)
const initialSources = <?= $sourcesJson ?>;
const initialDestinations = <?= $destinationsJson ?>;
const initialIntermediates = <?= $intermediatesJson ?>;
const initialCostMatrix = <?= $costMatrixJson ?>;

function generateTransshipMatrix(isEdit = false) {
    const nS = parseInt(document.getElementById('numSources').value) || 2;
    const nD = parseInt(document.getElementById('numDestinations').value) || 2;
    const nI = parseInt(document.getElementById('numIntermediates').value) || 0;
    const totalNodes = nS + nD + nI;

    let html = '<thead><tr><th>گره \\ گره</th>';
    for (let j = 0; j < totalNodes; j++) {
        let name = '';
        if (j < nS) name = `مبدأ ${j+1}`;
        else if (j < nS + nD) name = `مقصد ${j - nS + 1}`;
        else name = `میانی ${j - nS - nD + 1}`;
        html += `<th>${name}</th>`;
    }
    html += '<th class="supply-demand-cell">ظرفیت</th></tr></thead><tbody>';

    for (let i = 0; i < totalNodes; i++) {
        let rowName = '';
        if (i < nS) rowName = `مبدأ ${i+1}`;
        else if (i < nS + nD) rowName = `مقصد ${i - nS + 1}`;
        else rowName = `میانی ${i - nS - nD + 1}`;

        html += `<tr><th class="supply-demand-cell">${rowName}</th>`;
        for (let j = 0; j < totalNodes; j++) {
            const disabled = (i === j) ? 'disabled value="0"' : '';
            let val = '';
            
            // پر کردن مقدار هزینه اگر در حالت ویرایش باشیم
            if (isEdit && initialCostMatrix[i] && initialCostMatrix[i][j] !== null && initialCostMatrix[i][j] !== '') {
                // استفاده از parseFloat برای اطمینان از حذف صفرهای اضافی در سمت کلاینت
                val = parseFloat(initialCostMatrix[i][j]).toString();
            }
            
            html += `<td><input type="number" step="any" class="form-control form-control-sm cost-cell" data-i="${i}" data-j="${j}" value="${val}" ${disabled}></td>`;
        }
        
        // پر کردن مقدار ظرفیت اگر در حالت ویرایش باشیم
        let cap = 0;
        if (isEdit) {
            if (i < initialSources.length) cap = parseFloat(initialSources[i].capacity) || 0;
            else if (i < initialSources.length + initialDestinations.length) cap = parseFloat(initialDestinations[i - initialSources.length].capacity) || 0;
            else if (i < initialSources.length + initialDestinations.length + initialIntermediates.length) cap = parseFloat(initialIntermediates[i - initialSources.length - initialDestinations.length].capacity) || 0;
        }
        
        html += `<td><input type="number" class="form-control form-control-sm capacity-cell" data-i="${i}" value="${cap}"></td></tr>`;
    }
    html += '</tbody>';
    
    document.getElementById('transshipMatrix').innerHTML = html;
}

async function updateTransshipProject(id) {
    const nS = parseInt(document.getElementById('numSources').value);
    const nD = parseInt(document.getElementById('numDestinations').value);
    const nI = parseInt(document.getElementById('numIntermediates').value);

    const sources = [], destinations = [], intermediates = [];
    for (let i = 0; i < nS; i++) sources.push({ name: `مبدأ ${i+1}`, capacity: 0 });
    for (let j = 0; j < nD; j++) destinations.push({ name: `مقصد ${j+1}`, capacity: 0 });
    for (let k = 0; k < nI; k++) intermediates.push({ name: `میانی ${k+1}`, capacity: 0 });

    // خواندن ظرفیت‌ها از فرم
    document.querySelectorAll('.capacity-cell').forEach(el => {
        const i = parseInt(el.dataset.i);
        const val = parseFloat(el.value) || 0;
        if (i < nS) sources[i].capacity = val;
        else if (i < nS + nD) destinations[i - nS].capacity = val;
        else intermediates[i - nS - nD].capacity = val;
    });

    // خواندن ماتریس هزینه از فرم
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
        sources: sources,
        destinations: destinations,
        intermediates: intermediates,
        cost_matrix: costMatrix
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=transship&action=update&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ تغییرات با موفقیت ذخیره شد!');
            window.location.href = '<?= or_url("controller=transship&action=show&id=") ?>' + id;
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

// بارگذاری اولیه با پر کردن داده‌های موجود
document.addEventListener('DOMContentLoaded', () => generateTransshipMatrix(true));
</script>