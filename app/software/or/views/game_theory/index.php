<?php
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="or-page-header">
        <div>
            <h3 class="mb-1"><i class="fas fa-chess text-primary me-2"></i>نظریه بازی (Game Theory)</h3>
            <small class="text-muted">حل بازی‌های دو نفره (مجموع صفر و مجموع غیر صفر)</small>
        </div>
        <a href="<?= or_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-right me-1"></i><span class="d-none d-md-inline">بازگشت</span>
        </a>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-sliders-h me-2"></i>تعریف بازی</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">نوع بازی</label>
                        <select id="gameType" class="form-select form-select-sm" onchange="toggleGameType()">
                            <option value="zero_sum">دو نفره مجموع صفر (Zero-Sum)</option>
                            <option value="bimatrix">دو نفره مجموع غیر صفر (Bimatrix / Nash)</option>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">استراتژی‌های بازیکن ۱ (سطر)</label>
                            <input type="number" id="rows" class="form-control form-control-sm" value="2" min="2" max="5" onchange="buildMatrix()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">استراتژی‌های بازیکن ۲ (ستون)</label>
                            <input type="number" id="cols" class="form-control form-control-sm" value="2" min="2" max="5" onchange="buildMatrix()">
                        </div>
                    </div>

                    <div id="matrixBuilder" class="mb-3"></div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="saveCheck">
                        <label class="form-check-label small" for="saveCheck">ذخیره به‌عنوان پروژه</label>
                    </div>
                    <div class="mb-3" id="nameBox" style="display:none;">
                        <input type="text" id="projectName" class="form-control form-control-sm" placeholder="نام پروژه">
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" onclick="solveGame()">
                            <i class="fas fa-calculator me-1"></i> حل بازی
                        </button>
                    </div>
                    
                    <div class="mt-3 small">
                        <button class="btn btn-sm btn-outline-primary w-100 mb-1" onclick="loadPreset('zero')">نمونه: مجموع صفر (بدون نقطه زینی)</button>
                        <button class="btn btn-sm btn-outline-primary w-100" onclick="loadPreset('nash')">نمونه: مجموع غیر صفر (معضله زندانی)</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-trophy me-2"></i>نتایج و استراتژی بهینه</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-chess fa-3x mb-3"></i>
                        <p class="small mb-0">ماتریس پرداخت را تعریف و «حل بازی» را بزنید.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($projects)): ?>
    <div class="card border-0 shadow-sm mt-3 mt-md-4">
        <div class="card-header bg-white py-2 py-md-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-history me-2"></i>پروژه‌های قبلی</h6>
            <span class="badge bg-secondary"><?= count($projects) ?> مورد</span>
        </div>
        <div class="card-body p-2 p-md-3">
            <div class="row g-2 g-md-3">
                <?php foreach ($projects as $p):
                    $r = json_decode($p['result_json'] ?? '{}', true) ?: [];
                    $type = $r['type'] ?? 'unknown';
                    $mode = $r['game_mode'] ?? 'zero_sum';
                ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border h-100">
                            <div class="card-body p-3">
                                <h6 class="fw-bold mb-1 text-truncate"><?= htmlspecialchars($p['name']) ?></h6>
                                <div class="mb-2">
                                    <span class="badge bg-<?= $mode === 'bimatrix' ? 'info' : 'primary' ?>"><?= $mode === 'bimatrix' ? 'مجموع غیر صفر' : 'مجموع صفر' ?></span>
                                    <span class="badge bg-<?= $type === 'pure' || $type === 'pure_nash' ? 'success' : 'warning' ?>"><?= $type === 'pure' || $type === 'pure_nash' ? 'استراتژی خالص' : 'ترکیبی' ?></span>
                                </div>
                                <div class="d-flex gap-1 mt-2">
                                    <a href="<?= or_url('controller=game_theory&action=show&id=' . (int)$p['id']) ?>" class="btn btn-sm btn-outline-success flex-fill">
                                        <i class="fas fa-eye me-1"></i>مشاهده
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteProject(<?= (int)$p['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// ✅ تابع کمکی جاوااسکریپت (رفع خطای or_stat_box)
function or_stat_box(label, value, valueClass = '') {
    return `<div class="border rounded bg-light text-center p-2 h-100">
        <small class="d-block text-muted mb-1" style="font-size:.72rem;line-height:1.3;">${label}</small>
        <strong class="d-block ${valueClass}" style="line-height:1.3;word-break:break-word;">${value}</strong>
    </div>`;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleGameType() {
    buildMatrix();
}

function buildMatrix() {
    const gameType = document.getElementById('gameType').value;
    const rows = parseInt(document.getElementById('rows').value) || 2;
    const cols = parseInt(document.getElementById('cols').value) || 2;
    
    let html = '<div class="mb-2"><label class="form-label small mb-1">نام استراتژی‌های بازیکن ۱ (سطر)</label><div class="d-flex gap-2 flex-wrap">';
    for (let i = 0; i < rows; i++) {
        html += `<input type="text" class="form-control form-control-sm p1-strat flex-fill" value="استراتژی ${i + 1}">`;
    }
    html += '</div></div>';

    html += '<div class="mb-3"><label class="form-label small mb-1">نام استراتژی‌های بازیکن ۲ (ستون)</label><div class="d-flex gap-2 flex-wrap">';
    for (let j = 0; j < cols; j++) {
        html += `<input type="text" class="form-control form-control-sm p2-strat flex-fill" value="استراتژی ${j + 1}">`;
    }
    html += '</div></div>';

    if (gameType === 'zero_sum') {
        html += '<div class="alert alert-info py-2 small mb-2"><i class="fas fa-info-circle me-1"></i>مقادیر واردشده، پرداخت به <strong>بازیکن ۱</strong> است (پرداخت بازیکن ۲ قرینه آن است).</div>';
        html += '<div class="table-responsive"><table class="table table-sm table-bordered text-center mb-0 small">';
        html += '<thead class="table-light"><tr><th>بازیکن ۱ \\ ۲</th>';
        for (let j = 0; j < cols; j++) html += `<th>ستون ${j + 1}</th>`;
        html += '</tr></thead><tbody>';
        for (let i = 0; i < rows; i++) {
            html += `<tr><td class="fw-bold text-start">سطر ${i + 1}</td>`;
            for (let j = 0; j < cols; j++) {
                html += `<td><input type="number" step="any" class="form-control form-control-sm p-1 payoff-a" data-r="${i}" data-c="${j}" value="0"></td>`;
            }
            html += '</tr>';
        }
        html += '</tbody></table></div>';
    } else {
        html += '<div class="row g-2">';
        // ماتریس A
        html += '<div class="col-6"><div class="alert alert-primary py-2 small mb-2 text-center fw-bold">پرداخت بازیکن ۱</div>';
        html += '<table class="table table-sm table-bordered text-center mb-0 small">';
        html += '<thead class="table-light"><tr><th>بازیکن ۱ \\ ۲</th>';
        for (let j = 0; j < cols; j++) html += `<th>${j + 1}</th>`;
        html += '</tr></thead><tbody>';
        for (let i = 0; i < rows; i++) {
            html += `<tr><td class="fw-bold text-start">${i + 1}</td>`;
            for (let j = 0; j < cols; j++) {
                html += `<td><input type="number" step="any" class="form-control form-control-sm p-1 payoff-a" data-r="${i}" data-c="${j}" value="0"></td>`;
            }
            html += '</tr>';
        }
        html += '</tbody></table></div>';
        // ماتریس B
        html += '<div class="col-6"><div class="alert alert-success py-2 small mb-2 text-center fw-bold">پرداخت بازیکن ۲</div>';
        html += '<table class="table table-sm table-bordered text-center mb-0 small">';
        html += '<thead class="table-light"><tr><th>بازیکن ۱ \\ ۲</th>';
        for (let j = 0; j < cols; j++) html += `<th>${j + 1}</th>`;
        html += '</tr></thead><tbody>';
        for (let i = 0; i < rows; i++) {
            html += `<tr><td class="fw-bold text-start">${i + 1}</td>`;
            for (let j = 0; j < cols; j++) {
                html += `<td><input type="number" step="any" class="form-control form-control-sm p-1 payoff-b" data-r="${i}" data-c="${j}" value="0"></td>`;
            }
            html += '</tr>';
        }
        html += '</tbody></table></div></div>';
    }

    document.getElementById('matrixBuilder').innerHTML = html;
}

function loadPreset(type) {
    if (type === 'zero') {
        document.getElementById('gameType').value = 'zero_sum';
        document.getElementById('rows').value = 2;
        document.getElementById('cols').value = 2;
        buildMatrix();
        setTimeout(() => {
            document.querySelectorAll('.p1-strat')[0].value = 'تبلیغات گسترده';
            document.querySelectorAll('.p1-strat')[1].value = 'تبلیغات محدود';
            document.querySelectorAll('.p2-strat')[0].value = 'کاهش قیمت';
            document.querySelectorAll('.p2-strat')[1].value = 'حفظ قیمت';
            document.querySelector('.payoff-a[data-r="0"][data-c="0"]').value = 3;
            document.querySelector('.payoff-a[data-r="0"][data-c="1"]').value = -2;
            document.querySelector('.payoff-a[data-r="1"][data-c="0"]').value = -1;
            document.querySelector('.payoff-a[data-r="1"][data-c="1"]').value = 4;
        }, 50);
    } else if (type === 'nash') {
        document.getElementById('gameType').value = 'bimatrix';
        document.getElementById('rows').value = 2;
        document.getElementById('cols').value = 2;
        buildMatrix();
        setTimeout(() => {
            document.querySelectorAll('.p1-strat')[0].value = 'اعتراف';
            document.querySelectorAll('.p1-strat')[1].value = 'سکوت';
            document.querySelectorAll('.p2-strat')[0].value = 'اعتراف';
            document.querySelectorAll('.p2-strat')[1].value = 'سکوت';
            // Prisoner's Dilemma
            document.querySelector('.payoff-a[data-r="0"][data-c="0"]').value = -5; // P1 confess, P2 confess
            document.querySelector('.payoff-a[data-r="0"][data-c="1"]').value = 0;  // P1 confess, P2 silent
            document.querySelector('.payoff-a[data-r="1"][data-c="0"]').value = -10; // P1 silent, P2 confess
            document.querySelector('.payoff-a[data-r="1"][data-c="1"]').value = -1; // P1 silent, P2 silent

            document.querySelector('.payoff-b[data-r="0"][data-c="0"]').value = -5;
            document.querySelector('.payoff-b[data-r="0"][data-c="1"]').value = -10;
            document.querySelector('.payoff-b[data-r="1"][data-c="0"]').value = 0;
            document.querySelector('.payoff-b[data-r="1"][data-c="1"]').value = -1;
        }, 50);
    }
}

function getGameData() {
    const gameType = document.getElementById('gameType').value;
    const rows = parseInt(document.getElementById('rows').value) || 2;
    const cols = parseInt(document.getElementById('cols').value) || 2;
    
    const p1Strats = [];
    document.querySelectorAll('.p1-strat').forEach(el => p1Strats.push(el.value.trim()));
    
    const p2Strats = [];
    document.querySelectorAll('.p2-strat').forEach(el => p2Strats.push(el.value.trim()));
    
    const matrixA = [];
    for (let i = 0; i < rows; i++) {
        matrixA[i] = [];
        for (let j = 0; j < cols; j++) {
            const el = document.querySelector(`.payoff-a[data-r="${i}"][data-c="${j}"]`);
            matrixA[i][j] = parseFloat(el ? el.value : 0);
        }
    }

    let matrixB = [];
    if (gameType === 'bimatrix') {
        for (let i = 0; i < rows; i++) {
            matrixB[i] = [];
            for (let j = 0; j < cols; j++) {
                const el = document.querySelector(`.payoff-b[data-r="${i}"][data-c="${j}"]`);
                matrixB[i][j] = parseFloat(el ? el.value : 0);
            }
        }
    }

    return { gameType, p1Strats, p2Strats, matrixA, matrixB };
}

async function solveGame() {
    const { gameType, p1Strats, p2Strats, matrixA, matrixB } = getGameData();
    const payload = {
        game_type: gameType,
        p1_strats: p1Strats,
        p2_strats: p2Strats,
        matrix_a: matrixA,
        matrix_b: matrixB,
        save: document.getElementById('saveCheck').checked,
        name: document.getElementById('projectName').value.trim()
    };

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="small mt-2">در حال حل بازی...</p></div>';

    try {
        const res = await fetch('<?= or_url('controller=game_theory&action=solve') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) { 
            box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; 
            return; 
        }
        renderResult(data.result);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderResult(r) {
    const box = document.getElementById('resultBox');
    const isPure = (r.type === 'pure' || r.type === 'pure_nash');
    
    let html = `<div class="alert alert-${isPure ? 'success' : 'info'} py-2 small mb-3">
        <i class="fas fa-info-circle me-1"></i> ${escapeHtml(r.interpretation).replace(/\n/g, '<br>')}
    </div>`;

    html += `<div class="row g-1 g-md-2 mb-3">`;
    if (r.game_mode === 'zero_sum') {
        if (isPure) {
            html += `
                <div class="col-6 col-md-4">${or_stat_box('نوع تعادل', 'استراتژی خالص', 'text-success')}</div>
                <div class="col-6 col-md-4">${or_stat_box('نقطه زینی', `سطر ${r.saddle_point.row + 1}، ستون ${r.saddle_point.col + 1}`)}</div>
                <div class="col-6 col-md-4">${or_stat_box('ارزش بازی (V)', r.value_of_game, 'fw-bold text-primary')}</div>
            `;
        } else {
            html += `
                <div class="col-6 col-md-4">${or_stat_box('نوع تعادل', 'استراتژی ترکیبی', 'text-info')}</div>
                <div class="col-6 col-md-4">${or_stat_box('احتمال استراتژی ۱ بازیکن ۱', (r.p1_probs[0] * 100).toFixed(1) + '%')}</div>
                <div class="col-6 col-md-4">${or_stat_box('احتمال استراتژی ۲ بازیکن ۱', (r.p1_probs[1] * 100).toFixed(1) + '%')}</div>
                <div class="col-6 col-md-4">${or_stat_box('احتمال استراتژی ۱ بازیکن ۲', (r.p2_probs[0] * 100).toFixed(1) + '%')}</div>
                <div class="col-6 col-md-4">${or_stat_box('احتمال استراتژی ۲ بازیکن ۲', (r.p2_probs[1] * 100).toFixed(1) + '%')}</div>
                <div class="col-6 col-md-4">${or_stat_box('ارزش بازی (V)', r.value_of_game, 'fw-bold text-primary')}</div>
            `;
        }
    } else {
        html += `<div class="col-12">${or_stat_box('تعداد تعادل‌های نش یافت‌شده', r.nash_equilibria.length, 'fw-bold text-primary')}</div>`;
    }
    html += `</div>`;

    box.innerHTML = html;
}

async function deleteProject(id) {
    if (!confirm('حذف شود؟')) return;
    try {
        const res = await fetch('<?= or_url('controller=game_theory&action=delete&id=') ?>' + id);
        if (res.ok) location.reload();
        else alert('خطا در حذف');
    } catch (e) { alert('خطای شبکه: ' + e.message); }
}

document.getElementById('saveCheck').addEventListener('change', e => {
    document.getElementById('nameBox').style.display = e.target.checked ? '' : 'none';
});

document.addEventListener('DOMContentLoaded', () => { 
    buildMatrix(); 
});
</script>