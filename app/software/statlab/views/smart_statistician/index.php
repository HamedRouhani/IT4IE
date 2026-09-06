<?php
/**
 * StatLab - دستیار هوشمند آماری (نسخه ۲: تعریف دقیق پروژه + درصد واقعی)
 */
$samples = $samples ?? [];
$testNames = $testNames ?? [];
?>
<div class="container-fluid py-3 py-md-4">
    <div class="statlab-page-header">
        <h3 class="mb-0"><i class="fas fa-wand-magic-sparkles text-success me-2"></i>دستیار هوشمند آماری</h3>
        <a href="<?= stat_url('controller=dashboard') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-home me-1"></i><span class="d-none d-sm-inline">داشبورد</span>
        </a>
    </div>

    <div class="row g-3">
        <!-- ═══ ورودی ═══ -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-2 py-md-3">
                    <h6 class="mb-0"><i class="fas fa-pen me-2"></i>توصیف مسئله به زبان فارسی</h6>
                </div>
                <div class="card-body p-3">
                    <textarea id="smartText" class="form-control" rows="8"
                        placeholder="مثال: می‌خواهیم میانگین نمرات دو کلاس مستقل را مقایسه کنیم تا ببینیم تفاوت معنادار است یا خیر..."></textarea>

                    <div class="d-grid gap-2 mt-3">
                        <button class="btn btn-success" onclick="smartAnalyze()">
                            <i class="fas fa-brain me-1"></i> تحلیل هوشمند
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('smartText').value=''">
                            <i class="fas fa-eraser me-1"></i> پاک کردن
                        </button>
                    </div>

                    <hr class="my-3">
                    <small class="text-muted fw-bold d-block mb-2"> نمونه‌های آماده (کلیک کنید):</small>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($samples as $s): ?>
                            <button type="button" class="btn btn-sm btn-outline-success py-0"
                                    onclick="loadSample(<?= $s['id'] ?>)"><?= stat_e($s['label']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ نتیجه ═══ -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white py-2 py-md-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-robot me-2"></i>نتیجه تشخیص</h6>
                    <span id="confBadge" class="badge bg-light text-dark d-none"></span>
                </div>
                <div class="card-body p-3" id="smartResult">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-wand-magic-sparkles fa-3x mb-3"></i>
                        <p class="small mb-0">مسئله را بنویسید؛ دستیار تحلیل مناسب را تشخیص می‌دهد، سپس پروژه را دقیق تعریف و به آزمون می‌روید.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const SAMPLES = <?= json_encode($samples, JSON_UNESCAPED_UNICODE) ?>;
const TEST_NAMES = <?= json_encode($testNames, JSON_UNESCAPED_UNICODE) ?>;
let lastAnalysis = null;

function loadSample(id) {
    const s = SAMPLES.find(x => x.id === id);
    if (s) { document.getElementById('smartText').value = s.text; smartAnalyze(); }
}

function escapeHtml(s) { const d = document.createElement('div'); d.textContent = String(s ?? ''); return d.innerHTML; }
function testName(code) { return TEST_NAMES[code] || code; }

async function smartAnalyze() {
    const text = document.getElementById('smartText').value.trim();
    if (!text) { alert('⚠️ ابتدا مسئله را توصیف کنید.'); return; }

    const box = document.getElementById('smartResult');
    box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-success"></div><p class="small mt-2">در حال تحلیل متن...</p></div>';

    try {
        const res = await fetch('<?= stat_url('controller=smart_statistician&action=analyze') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ text })
        });
        const data = await res.json();
        if (!data.success) { box.innerHTML = `<div class="alert alert-danger py-2 small">❌ ${escapeHtml(data.error)}</div>`; return; }
        lastAnalysis = { ...data, text };
        renderSmart(data);
    } catch (e) {
        box.innerHTML = `<div class="alert alert-danger py-2 small">❌ خطای شبکه: ${escapeHtml(e.message)}</div>`;
    }
}

function renderSmart(d) {
    const box = document.getElementById('smartResult');
    const badge = document.getElementById('confBadge');
    badge.textContent = 'اطمینان: ' + d.confidence + '%';
    badge.classList.remove('d-none');

    const catLabels = {descriptive:'توصیفی', distribution:'توزیع', hypothesis:'آزمون فرض', regression:'رگرسیون'};

    let html = `
        <div class="alert alert-success py-2 mb-3">
            <i class="fas fa-check-circle me-1"></i>
            تحلیل پیشنهادی: <strong>${escapeHtml(d.detected_name)}</strong>
            <span class="badge bg-secondary ms-1">${escapeHtml(catLabels[d.category] ?? d.category)}</span>
        </div>

        <div class="row g-1 g-md-2 mb-3">
            <div class="col-6 col-md-4"><div class="stat-box"><small>سطح α</small><strong>${d.extracted_params.alpha}</strong></div></div>
            <div class="col-6 col-md-4"><div class="stat-box"><small>اطمینان</small><strong>${d.extracted_params.confidence}%</strong></div></div>
            <div class="col-6 col-md-4"><div class="stat-box"><small>H₁</small><strong>${escapeHtml({two:'دوطرفه',less:'چپ‌دم',greater:'راست‌دم'}[d.extracted_params.alternative])}</strong></div></div>
        </div>

        <h6 class="fw-bold small mb-2"><i class="fas fa-lightbulb me-1 text-warning"></i> چرا این تحلیل؟</h6>
        <div class="alert alert-info py-2 small mb-3">${escapeHtml(d.suggested.reason)}</div>`;

    if (d.warnings.length) {
        html += '<h6 class="fw-bold small mb-2"><i class="fas fa-exclamation-triangle me-1 text-danger"></i> هشدارها</h6>';
        d.warnings.forEach(w => { html += `<div class="alert alert-warning py-1 small mb-2">${escapeHtml(w)}</div>`; });
    }

    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-list-ol me-1 text-primary"></i> گام‌های بعدی</h6>
        <ol class="small mb-3 ps-3">` + d.next_steps.map(s => `<li>${escapeHtml(s)}</li>`).join('') + `</ol>`;

    // ═══ ✅ نمایش درصدی واقعی امتیاز گزینه‌ها ═══
    const entries = Object.entries(d.all_scores).filter(([, v]) => v > 0).sort((a, b) => b[1] - a[1]);
    const total = entries.reduce((s, [, v]) => s + v, 0) || 1;

    html += `<h6 class="fw-bold small mb-2"><i class="fas fa-chart-bar me-1 text-secondary"></i> امتیاز گزینه‌ها (درصد اطمینان)</h6><div class="mb-3">`;
    entries.slice(0, 5).forEach(([k, v]) => {
        const pct = (v / total) * 100;
        const isTop = k === d.detected_test;
        html += `
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center small mb-1">
                <span class="${isTop ? 'fw-bold text-success' : 'text-muted'}">
                    ${isTop ? '<i class="fas fa-check-circle me-1"></i>' : ''}${escapeHtml(testName(k))}
                </span>
                <span class="badge ${isTop ? 'bg-success' : 'bg-secondary'}">${pct.toFixed(1)}%</span>
            </div>
            <div class="progress" style="height:8px; border-radius:99px;">
                <div class="progress-bar ${isTop ? 'bg-success' : 'bg-secondary'}"
                     style="width:${Math.max(pct, 2)}%; border-radius:99px;"></div>
            </div>
        </div>`;
    });
    html += `</div>`;

    // ═══ ✅ گام دوم: تعریف دقیق پروژه قبل از انتقال ═══
    const defaultName = 'پروژه ' + d.detected_name + ' - ' + new Date().toLocaleDateString('fa-IR');
    html += `
    <h6 class="fw-bold small mb-2"><i class="fas fa-folder-plus me-1 text-success"></i> گام ۲: تعریف دقیق پروژه</h6>
    <div class="border rounded p-2 p-md-3 mb-3" style="background:#f8f9fa;">
        <div class="mb-2">
            <label class="form-label small mb-1">نام پروژه <span class="text-danger">*</span></label>
            <input type="text" id="projName" class="form-control form-control-sm" value="${escapeHtml(defaultName)}">
        </div>
        <div class="mb-2">
            <label class="form-label small mb-1">توضیحات پروژه</label>
            <textarea id="projDesc" class="form-control form-control-sm" rows="3">${escapeHtml(lastAnalysis.text)}</textarea>
        </div>
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small mb-1">سطح α</label>
                <select id="projAlpha" class="form-select form-select-sm">
                    ${[0.01, 0.05, 0.1].map(a =>
                        `<option value="${a}" ${Math.abs(a - d.extracted_params.alpha) < 1e-9 ? 'selected' : ''}>${a}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="col-6">
                <label class="form-label small mb-1">فرض جایگزین H₁</label>
                <select id="projAlt" class="form-select form-select-sm">
                    <option value="two" ${d.extracted_params.alternative === 'two' ? 'selected' : ''}>دوطرفه (≠)</option>
                    <option value="less" ${d.extracted_params.alternative === 'less' ? 'selected' : ''}>چپ‌دم (&lt;)</option>
                    <option value="greater" ${d.extracted_params.alternative === 'greater' ? 'selected' : ''}>راست‌دم (&gt;)</option>
                </select>
            </div>
        </div>
    </div>

    <div class="d-grid">
        <button class="btn btn-success" onclick="createSmartProject()">
            <i class="fas fa-folder-plus me-1"></i> ایجاد پروژه و انتقال به «${escapeHtml(d.detected_name)}»
        </button>
    </div>`;

    box.innerHTML = html;
}

// ═══ ✅ ابتدا پروژه تعریف می‌شود، سپس انتقال ═══
async function createSmartProject() {
    if (!lastAnalysis) return;

    const name = document.getElementById('projName').value.trim();
    if (!name) {
        alert('⚠️ نام پروژه الزامی است.');
        document.getElementById('projName').focus();
        return;
    }

    try {
        const res = await fetch('<?= stat_url('controller=smart_statistician&action=createProject') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                test: lastAnalysis.detected_test,
                category: lastAnalysis.category,
                text: lastAnalysis.text,
                name: name,
                description: document.getElementById('projDesc').value.trim(),
                params: {
                    ...lastAnalysis.extracted_params,
                    alpha: parseFloat(document.getElementById('projAlpha').value),
                    alternative: document.getElementById('projAlt').value
                },
                numbers: lastAnalysis.numbers_found
            })
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert('❌ ' + (data.error || 'خطا در ایجاد پروژه'));
        }
    } catch (e) { alert('❌ خطای شبکه: ' + e.message); }
}
</script>