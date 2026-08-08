<?php
$pageTitle = 'نتایج استخراج و تحلیل نیازمندی';
$activePage = 'requirement';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- دکمه بازگشت -->
            <div class="mb-3">
                <a href="/babok/public/?route=requirement" class="btn btn-secondary">
                    <i class="fas fa-arrow-right"></i> بازگشت به صفحه تحلیل
                </a>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-check"></i>
                        نتایج استخراج و تحلیل
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-success" id="resultStatus">تکمیل شده</span>
                    </div>
                </div>
                <div class="card-body" id="resultContent">
                    <!-- محتوای نتایج توسط JavaScript پر می‌شود -->
                    <div id="loadingResults" class="text-center" style="padding: 40px;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">در حال بارگذاری...</span>
                        </div>
                        <p class="mt-3">در حال بارگذاری نتایج...</p>
                    </div>
                    <div id="resultsDisplay" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- استایل‌ها -->
<!-- ========================================== -->
<style>
    .requirement-card {
        border-right: 4px solid #007bff;
        padding: 15px 20px;
        margin-bottom: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .requirement-card:hover {
        background: #e9ecef;
        transform: translateX(-5px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .requirement-card.functional { border-right-color: #007bff; }
    .requirement-card.non-functional { border-right-color: #ffc107; }
    
    .req-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
    }
    .req-title { font-weight: bold; color: #2c3e50; font-size: 16px; }
    .req-number { color: #6c757d; margin-left: 8px; }
    .req-type {
        font-size: 12px;
        padding: 3px 12px;
        border-radius: 20px;
        font-weight: bold;
    }
    .req-type.functional { background: #cce5ff; color: #004085; }
    .req-type.non-functional { background: #fff3cd; color: #856404; }
    .req-description {
        margin-top: 8px;
        color: #495057;
        line-height: 1.7;
        font-size: 14px;
    }
    .req-babok-ref {
        margin-top: 5px;
        font-size: 12px;
        color: #6c757d;
    }
    .req-babok-ref i {
        color: #17a2b8;
    }

    .technique-card {
        display: inline-block;
        background: #e9ecef;
        padding: 8px 15px;
        margin: 5px;
        border-radius: 20px;
        font-size: 14px;
        color: #495057;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }
    .technique-card:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
        transform: scale(1.05);
    }
    .technique-card .score {
        background: #28a745;
        color: white;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        margin-right: 5px;
    }
    .technique-card .category-badge {
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        margin-right: 5px;
    }

    .info-box {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 15px;
        border: 1px solid #e9ecef;
    }
    .info-box-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }
    .info-box-content { flex: 1; }
    .info-box-text { font-size: 14px; color: #6c757d; }
    .info-box-number { font-size: 24px; font-weight: bold; color: #2c3e50; }
    .bg-info { background: #17a2b8; }
    .bg-primary { background: #007bff; }
    .bg-warning { background: #ffc107; }
    .bg-success { background: #28a745; }

    .message {
        padding: 12px 18px;
        border-radius: 8px;
        margin: 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .message.info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    .message.warning { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
</style>

<!-- ========================================== -->
<!-- JavaScript -->
<!-- ========================================== -->
<script>
// ==========================================
// بارگذاری و نمایش نتایج
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const loadingDiv = document.getElementById('loadingResults');
    const displayDiv = document.getElementById('resultsDisplay');
    
    // دریافت داده از localStorage
    const stored = localStorage.getItem('analysisResult');
    
    if (!stored) {
        loadingDiv.innerHTML = `
            <div class="message warning">
                <i class="fas fa-exclamation-triangle"></i>
                هیچ نتیجه‌ای یافت نشد. لطفاً ابتدا یک تحلیل انجام دهید.
            </div>
            <a href="/babok/public/?route=requirement" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-right"></i> بازگشت به صفحه تحلیل
            </a>
        `;
        return;
    }
    
    try {
        const result = JSON.parse(stored);
        displayResults(result.data, result.text);
        loadingDiv.style.display = 'none';
        displayDiv.style.display = 'block';
    } catch (e) {
        loadingDiv.innerHTML = `
            <div class="message error">
                <i class="fas fa-exclamation-circle"></i>
                خطا در بارگذاری نتایج: ${e.message}
            </div>
            <a href="/babok/public/?route=requirement" class="btn btn-primary mt-3">
                <i class="fas fa-arrow-right"></i> بازگشت به صفحه تحلیل
            </a>
        `;
    }
});

function displayResults(data, text) {
    const req = data.requirements;
    const tech = data.techniques;
    const stats = data.stats;
    
    let html = '';
    
    // متن اصلی
    html += `
        <div class="alert alert-secondary">
            <strong>📝 متن ورودی:</strong>
            <p class="mt-2 mb-0" style="direction: rtl; white-space: pre-wrap;">${escapeHtml(text)}</p>
        </div>
    `;
    
    // آمار
    html += `
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">کل نیازمندی‌ها</span>
                        <span class="info-box-number">${stats.total}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-cogs"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">عملکردی</span>
                        <span class="info-box-number">${stats.functional}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-shield-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">غیرعملکردی</span>
                        <span class="info-box-number">${stats.non_functional}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-lightbulb"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">تکنیک‌های پیشنهادی</span>
                        <span class="info-box-number">${stats.techniques}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // نیازمندی‌ها
    html += `<h5 class="mt-3">📋 نیازمندی‌ها</h5>`;
    
    if (req.functional.length > 0) {
        html += `<h6 class="text-primary mt-3"><i class="fas fa-cogs"></i> عملکردی (${req.functional.length})</h6>`;
        req.functional.forEach((r, i) => {
            html += `
                <div class="requirement-card functional">
                    <div class="req-header">
                        <span class="req-title"><span class="req-number">${String(i+1).padStart(2,'0')}.</span> ${escapeHtml(r.title)}</span>
                        <span class="req-type functional">عملکردی</span>
                    </div>
                    <div class="req-description">${escapeHtml(r.description)}</div>
                    ${r.babok_reference ? `<div class="req-babok-ref"><i class="fas fa-book"></i> مرجع BABOK: ${escapeHtml(r.babok_reference)}</div>` : ''}
                    ${r.source_sentence ? `<div class="req-babok-ref"><i class="fas fa-quote-right"></i> منبع: "${escapeHtml(r.source_sentence)}"</div>` : ''}
                </div>
            `;
        });
    }
    
    if (req.non_functional.length > 0) {
        html += `<h6 class="text-warning mt-3"><i class="fas fa-shield-alt"></i> غیرعملکردی (${req.non_functional.length})</h6>`;
        req.non_functional.forEach((r, i) => {
            html += `
                <div class="requirement-card non-functional">
                    <div class="req-header">
                        <span class="req-title"><span class="req-number">${String(i+1).padStart(2,'0')}.</span> ${escapeHtml(r.title)}</span>
                        <span class="req-type non-functional">غیرعملکردی</span>
                    </div>
                    <div class="req-description">${escapeHtml(r.description)}</div>
                    ${r.babok_reference ? `<div class="req-babok-ref"><i class="fas fa-book"></i> مرجع BABOK: ${escapeHtml(r.babok_reference)}</div>` : ''}
                    ${r.source_sentence ? `<div class="req-babok-ref"><i class="fas fa-quote-right"></i> منبع: "${escapeHtml(r.source_sentence)}"</div>` : ''}
                </div>
            `;
        });
    }
    
    if (req.functional.length === 0 && req.non_functional.length === 0) {
        html += `<div class="message warning">⚠️ هیچ نیازمندی‌ای تشخیص داده نشد.</div>`;
    }
    
    // تکنیک‌ها
    html += `<h5 class="mt-4">💡 تکنیک‌های پیشنهادی</h5>`;
    
    if (tech.length > 0) {
        tech.forEach((t, i) => {
            const categoryColors = {
                'collaborative': 'info',
                'research': 'success',
                'experimental': 'warning',
                'management': 'secondary',
                'strategic': 'danger',
                'modeling': 'primary'
            };
            const color = categoryColors[t.technique.category] || 'secondary';
            const categoryNames = {
                'collaborative': 'همکاری',
                'research': 'تحقیقاتی',
                'experimental': 'تجربی',
                'management': 'مدیریتی',
                'strategic': 'استراتژیک',
                'modeling': 'مدل‌سازی'
            };
            html += `
                <div class="technique-card">
                    <span class="badge bg-${color} category-badge">${categoryNames[t.technique.category] || t.technique.category}</span>
                    ${i+1}. ${escapeHtml(t.technique.name)}
                    <span class="score">${t.score_percent}%</span>
                    ${t.technique.purpose ? `<small class="d-block text-muted mt-1">${escapeHtml(t.technique.purpose)}</small>` : ''}
                    ${t.reason ? `<small class="d-block text-muted mt-1"><i class="fas fa-info-circle"></i> ${escapeHtml(t.reason)}</small>` : ''}
                </div>
            `;
        });
    } else {
        html += `<div class="message info">💡 هیچ تکنیک خاصی پیشنهاد نشد.</div>`;
    }
    
    // ⭐ دکمه عملیات (بدون خروجی JSON)
    html += `
        <div class="row mt-4">
            <div class="col-12">
                <button class="btn btn-secondary" onclick="copyResult()">
                    <i class="fas fa-copy"></i> کپی متن
                </button>
                <a href="/babok/public/?route=requirement" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> تحلیل جدید
                </a>
            </div>
        </div>
    `;
    
    document.getElementById('resultsDisplay').innerHTML = html;
    document.getElementById('resultStatus').textContent = stats.total > 0 ? '✅ تکمیل شده' : '⚠️ بدون نتیجه';
}

// ==========================================
// توابع کمکی
// ==========================================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copyResult() {
    const stored = localStorage.getItem('analysisResult');
    if (!stored) {
        alert('هیچ نتیجه‌ای برای کپی وجود ندارد.');
        return;
    }
    
    const data = JSON.parse(stored);
    const req = data.data.requirements;
    const tech = data.data.techniques;
    
    let text = '📋 نتایج استخراج و تحلیل نیازمندی:\n\n';
    text += '📝 متن ورودی:\n' + data.text + '\n\n';
    text += '⚙️ نیازمندی‌های عملکردی:\n';
    req.functional.forEach((r, i) => {
        text += `${i+1}. ${r.title}\n   ${r.description}\n`;
        if (r.babok_reference) text += `   مرجع: ${r.babok_reference}\n`;
        text += '\n';
    });
    text += '🔧 نیازمندی‌های غیرعملکردی:\n';
    req.non_functional.forEach((r, i) => {
        text += `${i+1}. ${r.title}\n   ${r.description}\n`;
        if (r.babok_reference) text += `   مرجع: ${r.babok_reference}\n`;
        text += '\n';
    });
    text += '\n💡 تکنیک‌های پیشنهادی:\n';
    tech.forEach((t, i) => {
        text += `${i+1}. ${t.technique.name} (${t.score_percent}%)\n`;
        if (t.technique.purpose) text += `   ${t.technique.purpose}\n`;
        if (t.reason) text += `   دلیل: ${t.reason}\n`;
        text += '\n';
    });
    
    navigator.clipboard.writeText(text).then(() => {
        alert('✅ متن نتایج کپی شد.');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('✅ متن نتایج کپی شد.');
    });
}
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>