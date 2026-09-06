<?php
/**
 * StatLab - صفحه آمار توصیفی (نسخه نهایی - ریسپانسیو + اتصال به پروژه)
 * مسیر: app/software/statlab/views/descriptive/index.php
 */
$projects        = $projects ?? [];
$attachProject   = $attachProject ?? null;
$attachProjectId = $attachProjectId ?? 0;
?>
<div class="container-fluid py-3 py-md-4">
    <!-- سرصفحه -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3 class="mb-0 fs-4 fs-md-3">
            <i class="fas fa-chart-simple text-success me-2"></i>آمار توصیفی
        </h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i>
            <span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <!-- بنر اتصال به پروژه -->
    <?php if ($attachProject): ?>
        <div class="alert alert-success py-2 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>
                <i class="fas fa-link me-2"></i>
                داده‌های جدید به پروژه «<strong><?= stat_e($attachProject['name']) ?></strong>» اضافه خواهند شد.
            </span>
            <a href="<?= stat_url('controller=project&action=show&id=' . $attachProject['id']) ?>" 
               class="btn btn-sm btn-outline-success">
                <i class="fas fa-eye me-1"></i> مشاهده پروژه
            </a>
        </div>
    <?php endif; ?>

    <!-- بخش تنظیمات -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2 py-md-3">
            <h5 class="mb-0 fs-6"><i class="fas fa-cog me-2"></i>تنظیمات تحلیل</h5>
        </div>
        <div class="card-body">
            <div class="row g-2 g-md-3">
                <!-- select پروژه مقصد -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small mb-1">
                        <i class="fas fa-folder me-1"></i> پروژه مقصد
                    </label>
                    <select id="targetProject" class="form-select form-select-sm" onchange="onTargetChange()">
                        <option value="0">➕ ایجاد پروژه جدید</option>
                        <?php foreach ($projects as $p): ?>
                            <option value="<?= (int)$p['id'] ?>" 
                                <?= (int)$p['id'] === (int)$attachProjectId ? 'selected' : '' ?>>
                                📁 <?= stat_e($p['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- نام پروژه جدید -->
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold small mb-1">
                        <i class="fas fa-tag me-1"></i> نام پروژه جدید
                    </label>
                    <input type="text" id="projectName" class="form-control form-control-sm"
                           placeholder="فقط برای پروژه جدید"
                           value="تحلیل آماری <?= date('Y-m-d') ?>">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                        فقط در حالت «ایجاد پروژه جدید» استفاده می‌شود
                    </small>
                </div>
                <!-- تعداد متغیر -->
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold small mb-1">
                        <i class="fas fa-columns me-1"></i> متغیرها
                    </label>
                    <input type="number" id="numVariables" class="form-control form-control-sm"
                           min="1" max="20" value="1" onchange="generateVariableCards()">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">حداکثر ۲۰</small>
                </div>
                <!-- تعداد مشاهدات -->
                <div class="col-6 col-md-2">
                    <label class="form-label fw-bold small mb-1">
                        <i class="fas fa-list me-1"></i> مشاهدات
                    </label>
                    <input type="number" id="numObservations" class="form-control form-control-sm"
                           min="2" max="500" value="10" onchange="generateVariableCards()">
                    <small class="text-muted d-block" style="font-size: 0.7rem;">حداکثر ۵۰۰</small>
                </div>
            </div>
        </div>
    </div>

    <!-- دکمه‌های عملیاتی -->
    <div class="d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-info btn-sm flex-fill flex-md-grow-0" onclick="loadSampleData()">
            <i class="fas fa-magic me-1"></i> داده نمونه
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm flex-fill flex-md-grow-0" onclick="clearAllData()">
            <i class="fas fa-eraser me-1"></i> پاک کردن
        </button>
        <button type="button" class="btn btn-outline-warning btn-sm flex-fill flex-md-grow-0" onclick="addRowToAll()">
            <i class="fas fa-plus me-1"></i> افزودن ردیف
        </button>
    </div>

    <!-- بخش ورود داده‌ها (کارت‌های متغیرها) -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-white py-2 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fs-6"><i class="fas fa-keyboard me-2"></i>ورود داده‌ها</h5>
            <small class="text-muted">هر خط = یک عدد (پشتیبانی از فارسی/عربی)</small>
        </div>
        <div class="card-body">
            <div id="variableCardsContainer" class="row g-2 g-md-3">
                <!-- کارت‌های متغیرها به صورت داینامیک ایجاد می‌شوند -->
            </div>
        </div>
    </div>

    <!-- دکمه تحلیل -->
    <div class="d-grid gap-2 mb-3">
        <button type="button" class="btn btn-success btn-lg" onclick="analyzeAllData()">
            <i class="fas fa-calculator me-1"></i> محاسبه آمار توصیفی همه متغیرها
        </button>
    </div>

    <!-- بخش نتایج -->
    <div id="resultsSection" style="display: none;">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-success text-white py-2 py-md-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 fs-6"><i class="fas fa-chart-bar me-2"></i>نتایج تحلیل</h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light" onclick="scrollToProject()" id="viewProjectBtn" style="display:none;">
                        <i class="fas fa-folder-open me-1"></i> مشاهده پروژه
                    </button>
                    <button type="button" class="btn btn-sm btn-light" onclick="printResults()">
                        <i class="fas fa-print"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="resultsContainer">
                <!-- نتایج اینجا نمایش داده می‌شوند -->
            </div>
        </div>
    </div>
</div>

<!-- Loader -->
<div id="statLoader" class="d-none position-fixed top-0 start-0 w-100 h-100"
     style="background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; justify-content: center;">
    <div class="text-center text-white">
        <div class="spinner-border" style="width: 4rem; height: 4rem;" role="status"></div>
        <p class="mt-3" id="loaderText">در حال تحلیل...</p>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════
// توابع کمکی
// ═══════════════════════════════════════════════════════
function showLoader(text = 'در حال تحلیل...') {
    document.getElementById('loaderText').textContent = text;
    document.getElementById('statLoader').classList.remove('d-none');
}
function hideLoader() {
    document.getElementById('statLoader').classList.add('d-none');
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 80px; left: 50%; transform: translateX(-50%); z-index: 10000; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ═══════════════════════════════════════════════════════
// ایجاد کارت‌های متغیرها
// ═══════════════════════════════════════════════════════
function generateVariableCards() {
    const numVars = Math.min(parseInt(document.getElementById('numVariables').value) || 1, 20);
    const numObs  = Math.min(parseInt(document.getElementById('numObservations').value) || 10, 500);
    const container = document.getElementById('variableCardsContainer');

    let html = '';
    for (let v = 1; v <= numVars; v++) {
        const placeholders = Array(numObs).fill('').join('\n');
        html += `
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border h-100 variable-card" data-var="${v}">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                            <span class="badge bg-primary me-2">x${v}</span>
                            <input type="text" class="form-control form-control-sm var-name-input"
                                   id="varName_${v}" placeholder="نام متغیر" value="x${v}">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariable(${v})" title="حذف">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <label class="small text-muted mb-1 d-block">
                            <i class="fas fa-list me-1"></i> مقادیر (هر خط یک عدد):
                        </label>
                        <textarea class="form-control data-textarea font-monospace"
                                  id="data_${v}" rows="8"
                                  placeholder="مثال:&#10;۲۳.۵&#10;۴۵.۲&#10;۶۷.۸">${placeholders}</textarea>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted data-count" id="count_${v}">۰ داده</small>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary" onclick="pasteFromClipboard(${v})" title="چسباندن">
                                    <i class="fas fa-paste"></i>
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="clearVariable(${v})" title="پاک کردن">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    container.innerHTML = html;

    document.querySelectorAll('.data-textarea').forEach(textarea => {
        updateDataCount(textarea);
        textarea.addEventListener('input', () => updateDataCount(textarea));
    });
}

// ═══════════════════════════════════════════════════════
// شمارش تعداد داده‌ها
// ═══════════════════════════════════════════════════════
function updateDataCount(textarea) {
    const varId = textarea.id.split('_')[1];
    const countEl = document.getElementById('count_' + varId);
    const data = parseTextareaData(textarea.value);
    countEl.textContent = `${data.length} داده`;
    countEl.className = data.length > 0 ? 'text-success small data-count fw-bold' : 'text-muted small data-count';
}

// ═══════════════════════════════════════════════════════
// Parse داده‌ها (پشتیبانی از فارسی/عربی/انگلیسی)
// ═══════════════════════════════════════════════════════
function parseTextareaData(text) {
    const lines = text.split('\n');
    const values = [];
    const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
    const arabicDigits  = '٠١٢٣٤٥٦٧٨٩';

    lines.forEach(line => {
        const parts = line.trim().split(/[\s,;\t|]+/);
        parts.forEach(part => {
            if (part === '') return;
            // تبدیل فارسی/عربی به انگلیسی
            for (let i = 0; i < 10; i++) {
                part = part.replace(new RegExp(persianDigits[i], 'g'), i);
                part = part.replace(new RegExp(arabicDigits[i], 'g'), i);
            }
            // حذف علائم اضافی
            part = part.replace(/[^0-9.\-+eE]/g, '');
            if (part !== '' && !isNaN(part)) {
                values.push(parseFloat(part));
            }
        });
    });
    return values;
}

// ═══════════════════════════════════════════════════════
// داده نمونه
// ═══════════════════════════════════════════════════════
function loadSampleData() {
    document.getElementById('numVariables').value = 3;
    document.getElementById('numObservations').value = 10;
    if (!document.getElementById('targetProject').value || document.getElementById('targetProject').value === '0') {
        document.getElementById('projectName').value = 'نمونه: قد، وزن و سن';
    }
    generateVariableCards();

    document.getElementById('varName_1').value = 'قد (cm)';
    document.getElementById('varName_2').value = 'وزن (kg)';
    document.getElementById('varName_3').value = 'سن';

    const samples = {
        1: [165, 170, 172, 168, 175, 180, 178, 169, 171, 174],
        2: [60, 65, 68, 62, 70, 75, 72, 63, 66, 69],
        3: [25, 28, 30, 27, 32, 35, 33, 26, 29, 31]
    };
    for (let v = 1; v <= 3; v++) {
        document.getElementById(`data_${v}`).value = samples[v].join('\n');
        updateDataCount(document.getElementById(`data_${v}`));
    }
    showToast('✅ داده نمونه بارگذاری شد');
}

// ═══════════════════════════════════════════════════════
// عملیات پاک کردن
// ═══════════════════════════════════════════════════════
function clearAllData() {
    if (!confirm('آیا مطمئن هستید که می‌خواهید همه داده‌ها را پاک کنید؟')) return;
    document.querySelectorAll('.data-textarea').forEach(t => { t.value = ''; updateDataCount(t); });
}
function clearVariable(varId) {
    const t = document.getElementById(`data_${varId}`);
    t.value = ''; updateDataCount(t);
}
function addRowToAll() {
    document.querySelectorAll('.data-textarea').forEach(t => {
        t.value = t.value + (t.value ? '\n' : '');
        t.scrollTop = t.scrollHeight;
    });
}
function removeVariable(varId) {
    const n = parseInt(document.getElementById('numVariables').value);
    if (n <= 1) { alert('⚠️ حداقل یک متغیر باید باقی بماند.'); return; }
    if (!confirm(`آیا متغیر x${varId} را حذف کنیم؟`)) return;
    document.getElementById('numVariables').value = n - 1;
    generateVariableCards();
}

// ═══════════════════════════════════════════════════════
// چسباندن از کلیپ‌بورد
// ═══════════════════════════════════════════════════════
async function pasteFromClipboard(varId) {
    try {
        const text = await navigator.clipboard.readText();
        const t = document.getElementById(`data_${varId}`);
        t.value = text; updateDataCount(t);
        showToast('✅ از کلیپ‌بورد چسبانده شد');
    } catch (err) {
        alert('❌ دسترسی به کلیپ‌بورد ممکن نیست. لطفاً دستی Ctrl+V بزنید.');
    }
}

// ═══════════════════════════════════════════════════════
// تغییر پروژه مقصد
// ═══════════════════════════════════════════════════════
function onTargetChange() {
    const isNew = (parseInt(document.getElementById('targetProject').value, 10) || 0) === 0;
    document.getElementById('projectName').disabled = !isNew;
    if (!isNew) document.getElementById('projectName').value = '';
}

// ═══════════════════════════════════════════════════════
// تحلیل همه متغیرها
// ═══════════════════════════════════════════════════════
async function analyzeAllData() {
    const numVars = parseInt(document.getElementById('numVariables').value);
    let projectId = parseInt(document.getElementById('targetProject').value, 10) || 0;
    const projectName = document.getElementById('projectName').value.trim();

    if (projectId === 0 && !projectName) {
        alert('⚠️ برای ایجاد پروژه جدید، نام پروژه را وارد کنید.');
        return;
    }

    // جمع‌آوری داده‌ها
    const allData = {};
    let hasData = false;
    for (let v = 1; v <= numVars; v++) {
        const varName = (document.getElementById(`varName_${v}`).value || `x${v}`).trim();
        const values = parseTextareaData(document.getElementById(`data_${v}`).value);
        if (values.length >= 2) {
            allData[varName] = values;
            hasData = true;
        }
    }

    if (!hasData) {
        alert('⚠️ حداقل یک متغیر باید ۲ یا بیشتر داده معتبر داشته باشد.');
        return;
    }

    // نمایش loader
    const resultsSection = document.getElementById('resultsSection');
    const resultsContainer = document.getElementById('resultsContainer');
    resultsSection.style.display = 'block';
    resultsContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-success" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 mb-0">در حال تحلیل ${Object.keys(allData).length} متغیر...</p>
        </div>
    `;
    resultsSection.scrollIntoView({ behavior: 'smooth' });

    try {
        const results = {};
        for (const [varName, values] of Object.entries(allData)) {
            showLoader(`تحلیل «${varName}»...`);
            const res = await fetch('<?= stat_url('controller=descriptive&action=analyze') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    data: values.join(','),
                    variable_name: varName,
                    project_id: projectId,     // ✅ مهم: پروژه مقصد
                    project_name: projectName
                })
            });

            const data = await res.json();
            if (data.success) {
                if (projectId === 0) projectId = data.project_id;  // فقط بار اول
                results[varName] = {
                    stats: data.stats,
                    count: data.data_count,
                    project_id: data.project_id
                };
            } else {
                console.error(`خطا در ${varName}:`, data.error);
                showToast(`❌ خطا در «${varName}»: ${data.error}`, 'danger');
            }
        }
        hideLoader();

        if (Object.keys(results).length > 0) {
            displayAllResults(results, projectId);
            if (projectId > 0) {
                document.getElementById('viewProjectBtn').style.display = 'inline-block';
                document.getElementById('viewProjectBtn').onclick = () => {
                    window.location.href = '<?= stat_url('controller=project&action=show&id=') ?>' + projectId;
                };
            }
            showToast(`✅ ${Object.keys(results).length} متغیر با موفقیت تحلیل شد`);
        } else {
            resultsContainer.innerHTML = '<div class="alert alert-warning">⚠️ هیچ نتیجه‌ای به دست نیامد.</div>';
        }

    } catch (err) {
        hideLoader();
        resultsContainer.innerHTML = `<div class="alert alert-danger">❌ خطای شبکه: ${err.message}</div>`;
    }
}

// ═══════════════════════════════════════════════════════
// نمایش نتایج (Accordion ریسپانسیو)
// ═══════════════════════════════════════════════════════
function displayAllResults(results, projectId) {
    const container = document.getElementById('resultsContainer');
    const fmt = (v, d = 4) => v != null && v !== '' ? Number(v).toFixed(d) : '-';
    const entries = Object.entries(results);

    let html = `
        <div class="alert alert-success mb-3 py-2">
            <i class="fas fa-check-circle me-1"></i>
            <strong>${entries.length}</strong> متغیر در یک پروژه با موفقیت تحلیل شد.
            ${projectId ? `<a href="<?= stat_url('controller=project&action=show&id=') ?>${projectId}" class="alert-link ms-2">مشاهده پروژه →</a>` : ''}
        </div>

        <div class="card border mb-3">
            <div class="card-header bg-dark text-white py-2">
                <h6 class="mb-0"><i class="fas fa-table me-2"></i>مقایسه متغیرها</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>متغیر</th>
                                <th class="text-center">تعداد</th>
                                <th class="text-center">میانگین</th>
                                <th class="text-center">میانه</th>
                                <th class="text-center">انحراف</th>
                                <th class="text-center">چولگی</th>
                                <th class="text-center">پرت‌ها</th>
                            </tr>
                        </thead>
                        <tbody>
    `;

    for (const [varName, data] of entries) {
        const s = data.stats;
        const outliers = (s.outliers || []).length;
        html += `
            <tr>
                <td class="fw-bold">${varName}</td>
                <td class="text-center">${data.count}</td>
                <td class="text-center">${fmt(s.mean, 2)}</td>
                <td class="text-center">${fmt(s.median, 2)}</td>
                <td class="text-center">${fmt(s.std, 2)}</td>
                <td class="text-center">${fmt(s.skewness, 2)}</td>
                <td class="text-center">
                    ${outliers > 0 ? `<span class="badge bg-warning text-dark">${outliers}</span>` : '<span class="text-muted">-</span>'}
                </td>
            </tr>
        `;
    }
    html += '</tbody></table></div></div></div>';

    // Accordion برای هر متغیر
    html += '<div class="accordion" id="variablesAccordion">';
    let first = true;
    for (const [varName, data] of entries) {
        const s = data.stats;
        const collapseId = 'collapse_' + varName.replace(/[^a-zA-Z0-9]/g, '_') + '_' + Math.random().toString(36).substr(2, 5);
        const headerId   = 'header_' + collapseId;
        const idx = entries.findIndex(e => e[0] === varName) + 1;

        html += `
            <div class="accordion-item">
                <h2 class="accordion-header" id="${headerId}">
                    <button class="accordion-button ${first ? '' : 'collapsed'} py-2" type="button"
                            data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        <span class="badge bg-primary me-2">x${idx}</span>
                        <strong>${varName}</strong>
                        <span class="badge bg-success ms-2">${data.count} مشاهده</span>
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse ${first ? 'show' : ''}"
                     data-bs-parent="#variablesAccordion">
                    <div class="accordion-body p-2 p-md-3">
                        <h6 class="fw-bold small mb-2"><i class="fas fa-bullseye me-1 text-primary"></i> شاخص‌های مرکزی</h6>
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-6 col-md-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">میانگین</small>
                                    <strong class="text-primary">${fmt(s.mean)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">میانه</small>
                                    <strong class="text-success">${fmt(s.median)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">نما</small>
                                    <strong class="text-info">${s.mode !== null ? fmt(s.mode, 2) : '-'}</strong>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold small mb-2"><i class="fas fa-wave-square me-1 text-warning"></i> شاخص‌های پراکندگی</h6>
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-4 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">کمینه</small>
                                    <strong class="small">${fmt(s.min, 2)}</strong>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">بیشینه</small>
                                    <strong class="small">${fmt(s.max, 2)}</strong>
                                </div>
                            </div>
                            <div class="col-4 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">دامنه</small>
                                    <strong class="small">${fmt(s.range, 2)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">واریانس</small>
                                    <strong class="small">${fmt(s.variance)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">انحراف</small>
                                    <strong class="text-warning small">${fmt(s.std)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem">CV</small>
                                    <strong class="small">${s.mean != 0 ? fmt((s.std / Math.abs(s.mean)) * 100, 2) + '%' : '-'}</strong>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold small mb-2"><i class="fas fa-layer-group me-1 text-info"></i> چهارک‌ها</h6>
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">Q1 (25%)</small>
                                    <strong class="small">${fmt(s.quartiles.q1)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">Q2 (50%)</small>
                                    <strong class="small">${fmt(s.quartiles.q2)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">Q3 (75%)</small>
                                    <strong class="small">${fmt(s.quartiles.q3)}</strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">IQR</small>
                                    <strong class="small">${fmt(s.quartiles.q3 - s.quartiles.q1, 2)}</strong>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold small mb-2"><i class="fas fa-shapes me-1 text-secondary"></i> شاخص‌های شکلی</h6>
                        <div class="row g-1 g-md-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">چولگی</small>
                                    <strong class="${Math.abs(s.skewness) < 0.5 ? 'text-success' : 'text-warning'}">${fmt(s.skewness, 3)}</strong>
                                    <small class="d-block text-muted" style="font-size:0.65rem;">
                                        ${Math.abs(s.skewness) < 0.5 ? '✓ متقارن' : (s.skewness > 0 ? '→ چوله راست' : '← چوله چپ')}
                                    </small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block" style="font-size:0.7rem;">کشیدگی</small>
                                    <strong class="${Math.abs(s.kurtosis) < 0.5 ? 'text-success' : 'text-warning'}">${fmt(s.kurtosis, 3)}</strong>
                                    <small class="d-block text-muted" style="font-size:0.65rem;">
                                        ${Math.abs(s.kurtosis) < 0.5 ? '✓ نرمال' : (s.kurtosis > 0 ? '🔺 کشیده' : '🔻 پهن')}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold small mb-2"><i class="fas fa-exclamation-triangle me-1 text-danger"></i> داده‌های پرت</h6>
                        ${s.outliers && s.outliers.length > 0
                            ? `<div class="alert alert-warning mb-0 py-2 small">
                                <strong>${s.outliers.length}</strong> داده پرت:
                                <span class="font-monospace">${s.outliers.slice(0, 10).map(v => fmt(v, 2)).join(', ')}${s.outliers.length > 10 ? '...' : ''}</span>
                               </div>`
                            : '<div class="alert alert-success mb-0 py-2 small">✅ داده پرت شناسایی نشد.</div>'
                        }
                    </div>
                </div>
            </div>
        `;
        first = false;
    }
    html += '</div>';
    container.innerHTML = html;
}

function scrollToProject() {
    const btn = document.getElementById('viewProjectBtn');
    if (btn && btn.onclick) btn.onclick();
}
function printResults() { window.print(); }

// ═══════════════════════════════════════════════════════
// اجرای اولیه
// ═══════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
    generateVariableCards();
    onTargetChange();
});
</script>