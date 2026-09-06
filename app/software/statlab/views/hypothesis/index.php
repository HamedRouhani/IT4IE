<?php
/**
 * StatLab - آزمون فرض (ریسپانسیو + بارگذاری داده از پروژه موجود)
 */
$tests = $tests ?? [];
$projects = $projects ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="statlab-page-header">
        <h3 class="mb-0"><i class="fas fa-scale-balanced text-success me-2"></i>آزمون فرض</h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- ═══ فرم ═══ -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-flask me-2"></i>تنظیمات آزمون</h6>
                </div>
                <div class="card-body p-3">

                    <div class="mb-3">
                        <label class="form-label fw-bold small mb-1">نوع آزمون</label>
                        <select id="testSelect" class="form-select form-select-sm" onchange="renderTestForm()">
                            <?php foreach ($tests as $code => $t): ?>
                                <option value="<?= $code ?>">
                                    <?= stat_e($t['name_fa']) ?> (<?= $t['group'] === 'parametric' ? 'پارامتری' : 'ناپارامتری' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- ✅ بارگذاری داده از پروژه موجود -->
                    <div class="p-2 mb-3 border rounded" style="background: #f0fdf4;">
                        <label class="form-label fw-bold small mb-1">
                            <i class="fas fa-database me-1 text-success"></i> استفاده از داده‌های پروژه موجود
                        </label>
                        <div class="d-flex gap-2">
                            <select id="sourceProject" class="form-select form-select-sm">
                                <option value="0">انتخاب پروژه...</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-sm btn-success text-nowrap" onclick="loadProjectDatasets()">
                                <i class="fas fa-download me-1"></i> بارگذاری
                            </button>
                        </div>
                        <div id="datasetPicker" class="mt-2"></div>
                    </div>

                    <div id="dynamicInputs"></div>

                    <div class="row g-2 mb-3">
                        <div class="col-6" id="altBox">
                            <label class="form-label small mb-1">فرض جایگزین H₁</label>
                            <select id="alternative" class="form-select form-select-sm">
                                <option value="two">دوطرفه (≠)</option>
                                <option value="less">چپ‌دم (&lt;)</option>
                                <option value="greater">راست‌دم (&gt;)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">سطح α</label>
                            <select id="alpha" class="form-select form-select-sm">
                                <option value="0.05">0.05</option>
                                <option value="0.01">0.01</option>
                                <option value="0.10">0.10</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small mb-1">ذخیره در پروژه</label>
                            <select id="targetProject" class="form-select form-select-sm" onchange="onTargetChange()">
                                <option value="0">➕ پروژه جدید</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= (int)$p['id'] ?>"><?= stat_e($p['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small mb-1">نام پروژه جدید</label>
                            <input type="text" id="projectName" class="form-control form-control-sm" placeholder="اختیاری">
                        </div>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-success" onclick="runTest()">
                            <i class="fas fa-play me-1"></i> اجرای آزمون
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ نتایج ═══ -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-poll me-2"></i>نتیجه آزمون</h6>
                </div>
                <div class="card-body p-3" id="resultBox">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-scale-balanced fa-3x mb-3"></i>
                        <p class="small mb-0">
                            داده‌ها را دستی وارد کنید یا از یک پروژه موجود بارگذاری کنید، سپس «اجرای آزمون» را بزنید.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ═══ متادیتای ورودی هر آزمون ═══
const INPUT_SCHEMA = {
    one_sample_t: [{t:'ta', k:'data1', l:'داده‌های نمونه'}, {t:'n', k:'mu0', l:'میانگین فرضی μ₀', d:0}],
    two_sample_t: [{t:'ta', k:'data1', l:'داده‌های گروه ۱'}, {t:'ta', k:'data2', l:'داده‌های گروه ۲'}],
    paired_t:     [{t:'ta', k:'data1', l:'گروه ۱ (قبل)'}, {t:'ta', k:'data2', l:'گروه ۲ (بعد)'}],
    one_prop_z:   [{t:'n', k:'x', l:'تعداد موفقیت x', d:0}, {t:'n', k:'n', l:'حجم نمونه n', d:0}, {t:'n', k:'p0', l:'نسبت فرضی p₀', d:0.5}],
    f_var:        [{t:'ta', k:'data1', l:'داده‌های گروه ۱'}, {t:'ta', k:'data2', l:'داده‌های گروه ۲'}],
    chi2_gof:     [{t:'ta', k:'observed', l:'فراوانی مشاهده‌شده'}, {t:'ta', k:'expected', l:'فراوانی موردانتظار (اختیاری)'}],
    chi2_indep:   [{t:'ta', k:'matrix', l:'جدول توافقی (هر خط یک سطر)'}],
    mann_whitney: [{t:'ta', k:'data1', l:'داده‌های گروه ۱'}, {t:'ta', k:'data2', l:'داده‌های گروه ۲'}],
};
const NO_ALT = ['f_var', 'chi2_gof', 'chi2_indep'];
let loadedDatasets = [];

// ═══ توابع کمکی ═══
function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = String(s ?? '');
    return div.innerHTML;
}
function showToast(msg, type = 'success') {
    const t = document.createElement('div');
    t.className = `alert alert-${type} position-fixed`;
    t.style.cssText = 'top:80px;left:50%;transform:translateX(-50%);z-index:10000;min-width:280px;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.transition = 'opacity .3s'; t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}

// ═══ ساخت فرم داینامیک آزمون ═══
function renderTestForm() {
    const test = document.getElementById('testSelect').value;
    document.getElementById('dynamicInputs').innerHTML = INPUT_SCHEMA[test].map(f => {
        if (f.t === 'ta') {
            return `<div class="mb-2"><label class="form-label small mb-1">${f.l}</label>
                <textarea id="in_${f.k}" class="form-control form-control-sm font-monospace" rows="4"
                    placeholder="دستی وارد کنید یا از پروژه بارگذاری کنید"></textarea></div>`;
        }
        return `<div class="mb-2"><label class="form-label small mb-1">${f.l}</label>
            <input type="number" step="any" id="in_${f.k}" class="form-control form-control-sm" value="${f.d ?? 0}"></div>`;
    }).join('');
    document.getElementById('altBox').style.display = NO_ALT.includes(test) ? 'none' : '';
    renderDatasetPicker(); // بازچینی دکمه‌های جایگذاری مطابق فیلدهای جدید
}

// ═══ ✅ بارگذاری داده‌ها از پروژه موجود ═══
async function loadProjectDatasets() {
    const pid = parseInt(document.getElementById('sourceProject').value) || 0;
    if (!pid) { showToast('⚠️ ابتدا یک پروژه انتخاب کنید.', 'warning'); return; }

    try {
        const res = await fetch('<?= stat_url('controller=project&action=datasets&id=') ?>' + pid);
        const data = await res.json();
        if (!data.success) { showToast('❌ ' + data.error, 'danger'); return; }

        loadedDatasets = data.datasets;
        renderDatasetPicker();

        // همزمان پروژه مقصد ذخیره را هم روی همین پروژه بگذار
        document.getElementById('targetProject').value = String(pid);
        onTargetChange();
        showToast(`✅ ${loadedDatasets.length} مجموعه داده بارگذاری شد`);
    } catch (e) {
        showToast('❌ خطای شبکه: ' + e.message, 'danger');
    }
}

// ═══ نمایش لیست داده‌های پروژه با دکمه‌های جایگذاری ═══
function renderDatasetPicker() {
    const box = document.getElementById('datasetPicker');
    if (!loadedDatasets.length) { box.innerHTML = ''; return; }

    const test = document.getElementById('testSelect').value;
    const targets = INPUT_SCHEMA[test].filter(f => f.t === 'ta' && f.k !== 'matrix');

    if (!targets.length) {
        box.innerHTML = '<div class="alert alert-warning py-1 small mb-0">این آزمون ورودی ماتریسی دارد؛ بارگذاری خودکار پشتیبانی نمی‌شود.</div>';
        return;
    }

    let html = '<small class="text-muted d-block mb-1">روی مقصد هر داده کلیک کنید:</small>';
    loadedDatasets.forEach((ds, idx) => {
        html += `<div class="d-flex flex-wrap align-items-center gap-1 mb-1 p-1 border rounded bg-white">
            <span class="small fw-bold flex-grow-1" style="min-width:120px;">
                ${escapeHtml(ds.name)} <span class="badge bg-secondary">${ds.data.length}</span>
            </span>`;
        targets.forEach(tg => {
            html += `<button type="button" class="btn btn-sm btn-outline-primary py-0"
                        onclick="assignDataset(${idx}, '${tg.k}')">→ ${escapeHtml(tg.l)}</button>`;
        });
        html += '</div>';
    });
    box.innerHTML = html;
}

// ═══ جایگذاری داده در فیلد مربوطه ═══
function assignDataset(idx, key) {
    const ds = loadedDatasets[idx];
    const el = document.getElementById('in_' + key);
    if (!el || !ds) return;
    el.value = ds.data.join('\n');
    showToast(`✅ «${escapeHtml(ds.name)}» در «${escapeHtml(key)}» قرار گرفت`);
}

function onTargetChange() {
    const isNew = (parseInt(document.getElementById('targetProject').value) || 0) === 0;
    document.getElementById('projectName').disabled = !isNew;
}

// ═══ اجرای آزمون ═══
async function runTest() {
    const test = document.getElementById('testSelect').value;
    const payload = {
        test,
        alpha: parseFloat(document.getElementById('alpha').value),
        alternative: document.getElementById('alternative').value,
        project_id: parseInt(document.getElementById('targetProject').value) || 0,
        project_name: document.getElementById('projectName').value.trim(),
    };
    INPUT_SCHEMA[test].forEach(f => { payload[f.k] = document.getElementById('in_' + f.k).value; });

    const box = document.getElementById('resultBox');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success"></div><p class="small mt-2">در حال اجرا...</p></div>';

    try {
        const res = await fetch('<?= stat_url('controller=hypothesis&action=analyze') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!data.success) {
            box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`;
            return;
        }
        renderResult(data);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

// ═══ نمایش نتیجه ═══
function renderResult(data) {
    const r = data.result;
    const box = document.getElementById('resultBox');
    const reject = r.conclusion === 'reject';

    let html = `
        <div class="alert ${reject ? 'alert-success' : 'alert-warning'} py-2 mb-3">
            <strong>${escapeHtml(data.test_name)}</strong> —
            ${reject ? '✅ H0 رد می‌شود (معنادار)' : '⛔ H0 رد نمی‌شود (نامعنادر)'}
        </div>
        <div class="row g-1 g-md-2 mb-3">
            <div class="col-6 col-md-3"><div class="stat-box"><small>آماره (${r.statistic_name})</small><strong class="text-primary">${Number(r.statistic).toFixed(4)}</strong></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><small>p-value</small><strong class="${r.p_value < r.alpha ? 'text-success' : 'text-danger'}">${r.p_value < 0.000001 ? Number(r.p_value).toExponential(2) : Number(r.p_value).toFixed(5)}</strong></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><small>درجه آزادی</small><strong>${Array.isArray(r.df) ? r.df.join(' , ') : (r.df ?? '-')}</strong></div></div>
            <div class="col-6 col-md-3"><div class="stat-box"><small>مقدار بحرانی</small><strong class="small">${Array.isArray(r.critical) ? r.critical.join(' , ') : r.critical}</strong></div></div>
        </div>`;

    if (r.extra && Object.keys(r.extra).length) {
        html += '<h6 class="fw-bold small mb-2"><i class="fas fa-info-circle me-1 text-info"></i> جزئیات:</h6><div class="row g-1 g-md-2 mb-3">';
        for (const [k, v] of Object.entries(r.extra)) {
            const val = Array.isArray(v) ? `[${v.join(' , ')}]` : v;
            html += `<div class="col-6 col-md-3"><div class="stat-box"><small>${escapeHtml(k)}</small><strong class="small">${val}</strong></div></div>`;
        }
        html += '</div>';
    }

    (r.warnings || []).forEach(w => { html += `<div class="alert alert-warning py-2 small mb-2">${escapeHtml(w)}</div>`; });

    html += `<div class="alert alert-info py-2 small mb-0"><pre class="mb-0" style="white-space: pre-wrap;">${escapeHtml(r.interpretation_fa)}</pre></div>`;
    box.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', () => { renderTestForm(); onTargetChange(); });
</script>