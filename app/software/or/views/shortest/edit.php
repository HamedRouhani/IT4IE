<?php 
$nodeMap = [];
foreach ($nodes as $n) $nodeMap[$n['id']] = $n['name'];
$edgesData = [];
foreach ($edges as $edge) {
    $edgesData[] = [
        'from' => array_search($edge['source_id'], array_column($nodes, 'id')),
        'to' => array_search($edge['destination_id'], array_column($nodes, 'id')),
        'weight' => $edge['cost']
    ];
}
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit text-warning"></i> ویرایش پروژه کوتاه‌ترین مسیر</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?= or_url('controller=shortest') ?>">کوتاه‌ترین مسیر</a></li>
                    <li class="breadcrumb-item active">ویرایش <?= or_e($project['name']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">نام پروژه</label>
                        <input type="text" id="projName" class="form-control" value="<?= or_e($project['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">توضیحات</label>
                        <textarea id="projDesc" class="form-control" rows="2"><?= or_e($project['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">تعداد گره‌ها</label>
                        <input type="number" id="numNodes" class="form-control" value="<?= count($nodes) ?>" min="2" max="20">
                    </div>
                    <button type="button" class="btn btn-or-primary w-100 mb-2" onclick="generateGraph()">
                        <i class="fas fa-sync-alt"></i> بازسازی گره‌ها
                    </button>
                    <button type="button" class="btn btn-outline-success w-100" onclick="addEdge()">
                        <i class="fas fa-plus"></i> افزودن یال
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">گره‌ها و یال‌ها</h5>
                    <div class="mb-4">
                        <h6 class="text-muted">گره‌ها:</h6>
                        <div id="nodesList" class="d-flex flex-wrap gap-2 mb-3"></div>
                    </div>
                    <div class="mb-3">
                        <h6 class="text-muted">یال‌ها:</h6>
                        <div id="edgesList" class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr><th>#</th><th>مبدأ</th><th>مقصد</th><th>وزن</th><th>عملیات</th></tr>
                                </thead>
                                <tbody id="edgesTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="<?= or_url('controller=shortest&action=show&id=' . $project['id']) ?>" class="btn btn-outline-secondary">بازگشت</a>
                        <button type="button" class="btn btn-or-success" onclick="updateShortestProject(<?= $project['id'] ?>)">
                            <i class="fas fa-save"></i> ذخیره تغییرات
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal افزودن یال -->
<div class="modal fade" id="edgeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">افزودن یال جدید</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">گره مبدأ</label><select id="edgeFrom" class="form-select"></select></div>
                <div class="mb-3"><label class="form-label">گره مقصد</label><select id="edgeTo" class="form-select"></select></div>
                <div class="mb-3"><label class="form-label">وزن</label><input type="number" id="edgeWeight" class="form-control" step="any" min="0" value="1"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="button" class="btn btn-primary" onclick="saveEdge()">ذخیره یال</button>
            </div>
        </div>
    </div>
</div>

<script>
let nodes = <?= json_encode($nodes) ?>;
let edges = <?= json_encode($edgesData) ?>;
let edgeModal;

document.addEventListener('DOMContentLoaded', () => {
    edgeModal = new bootstrap.Modal(document.getElementById('edgeModal'));
    renderNodes();
    renderEdges();
});

function generateGraph() {
    const numNodes = parseInt(document.getElementById('numNodes').value);
    nodes = [];
    for (let i = 0; i < numNodes; i++) nodes.push({ id: i, name: `گره ${i + 1}` });
    edges = [];
    renderNodes();
    renderEdges();
}

function renderNodes() {
    document.getElementById('nodesList').innerHTML = nodes.map(n => 
        `<span class="badge bg-primary fs-6 p-2">${n.name}</span>`
    ).join('');
}

function addEdge() {
    if (nodes.length < 2) { alert('حداقل ۲ گره نیاز است.'); return; }
    document.getElementById('edgeFrom').innerHTML = nodes.map(n => `<option value="${n.id}">${n.name}</option>`).join('');
    document.getElementById('edgeTo').innerHTML = nodes.map(n => `<option value="${n.id}">${n.name}</option>`).join('');
    document.getElementById('edgeWeight').value = 1;
    edgeModal.show();
}

function saveEdge() {
    const from = parseInt(document.getElementById('edgeFrom').value);
    const to = parseInt(document.getElementById('edgeTo').value);
    const weight = parseFloat(document.getElementById('edgeWeight').value);
    if (from === to) { alert('مبدأ و مقصد نمی‌توانند یکسان باشند.'); return; }
    if (weight < 0) { alert('وزن نمی‌تواند منفی باشد.'); return; }
    edges.push({ from, to, weight });
    renderEdges();
    edgeModal.hide();
}

function removeEdge(idx) {
    edges.splice(idx, 1);
    renderEdges();
}

function renderEdges() {
    const tbody = document.getElementById('edgesTableBody');
    if (edges.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">هنوز یالی اضافه نشده است.</td></tr>';
        return;
    }
    tbody.innerHTML = edges.map((e, idx) => `
        <tr>
            <td>${idx + 1}</td>
            <td>${nodes[e.from].name}</td>
            <td>${nodes[e.to].name}</td>
            <td>${e.weight}</td>
            <td><button class="btn btn-sm btn-outline-danger" onclick="removeEdge(${idx})"><i class="fas fa-times"></i></button></td>
        </tr>
    `).join('');
}

async function updateShortestProject(id) {
    const payload = {
        name: document.getElementById('projName').value,
        description: document.getElementById('projDesc').value,
        nodes: nodes,
        edges: edges
    };

    const btn = event.target;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ذخیره...';
    btn.disabled = true;

    try {
        const res = await fetch('<?= or_url("controller=shortest&action=update&id=") ?>' + id, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            alert('✅ تغییرات ذخیره شد!');
            window.location.href = '<?= or_url("controller=shortest&action=show&id=") ?>' + id;
        } else {
            alert('❌ ' + data.error);
        }
    } catch (e) {
        alert('❌ خطای شبکه');
    } finally {
        btn.innerHTML = '<i class="fas fa-save"></i> ذخیره تغییرات';
        btn.disabled = false;
    }
}
</script>