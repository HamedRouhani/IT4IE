<?php
/**
 * رابط کاربری مدلسازی هوشمند OR - نسخه نهایی
 */
?>

<div class="container-fluid py-4">
    <!-- هدر -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body p-4">
            <h2 class="mb-2">
                <i class="fas fa-brain"></i> مدلسازی هوشمند مسائل تحقیق در عملیات
            </h2>
            <p class="mb-0 opacity-90" style="color: white">
                مسئله خود را به زبان فارسی توصیف کنید. سیستم به صورت هوشمند نوع مسئله، مدل ریاضی و روش حل را تشخیص می‌دهد.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- بخش ورودی متن -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-pen-fancy text-primary"></i> توصیف مسئله شما
                    </h5>
                    <textarea id="problemText" class="form-control" rows="8" 
                        placeholder="مثال: یک شرکت تولیدی دارای ۳ کارخانه در شهرهای تهران، اصفهان و شیراز است که به ترتیب ظرفیت تولید ۲۰۰، ۱۵۰ و ۱۸۰ تن در روز دارند. این شرکت باید محصولات خود را به ۴ انبار در مشهد، تبریز، اهواز و رشت برساند که تقاضای آن‌ها به ترتیب ۱۲۰، ۱۰۰، ۱۴۰ و ۱۷۰ تن است. هزینه حمل هر تن محصول بین هر کارخانه و انبار در جدول زیر آمده است. هدف کمینه‌سازی کل هزینه حمل است."></textarea>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary btn-lg" id="analyzeBtn" onclick="analyzeProblem()">
                            <i class="fas fa-magic"></i> تحلیل هوشمند
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearAll()">
                            <i class="fas fa-eraser"></i> پاک کردن
                        </button>
                    </div>

                    <!-- نمونه‌های سریع -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-lightbulb text-warning"></i> نمونه‌های آماده (کلیک کنید):
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText(1)">
                                <i class="fas fa-truck"></i> حمل و نقل
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText(2)">
                                <i class="fas fa-users"></i> تخصیص
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText(3)">
                                <i class="fas fa-route"></i> کوتاه‌ترین مسیر
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText(4)">
                                <i class="fas fa-chart-line"></i> برنامه‌ریزی خطی
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- نتایج تحلیل -->
            <div id="analysisResult" class="d-none"></div>
        </div>

        <!-- ستون کناری: راهنما -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle text-info"></i> راهنمای استفاده</h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0 ps-3">
                        <li class="mb-2">مسئله خود را به صورت یک پاراگراف کامل بنویسید</li>
                        <li class="mb-2">شامل منابع، مقاصد، هزینه‌ها و هدف باشد</li>
                        <li class="mb-2">اعداد و نام‌ها را به فارسی بنویسید</li>
                        <li class="mb-2">روی "تحلیل هوشمند" کلیک کنید</li>
                        <li>نتایج شامل نوع مسئله، درصد اطمینان و مدل ریاضی نمایش داده می‌شود</li>
                    </ol>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-question-circle text-success"></i> انواع مسائل پشتیبانی‌شده</h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <strong> حمل و نقل</strong>
                        <small class="d-block text-muted">توزیع کالا از چند مبدأ به چند مقصد</small>
                    </div>
                    <div class="list-group-item">
                        <strong>👷 تخصیص</strong>
                        <small class="d-block text-muted">اختصاص یک‌به‌یک عوامل به وظایف</small>
                    </div>
                    <div class="list-group-item">
                        <strong>🛣️ کوتاه‌ترین مسیر</strong>
                        <small class="d-block text-muted">یافتن بهینه‌ترین مسیر در شبکه</small>
                    </div>
                    <div class="list-group-item">
                        <strong>📈 برنامه‌ریزی خطی</strong>
                        <small class="d-block text-muted">بهینه‌سازی با قیود خطی</small>
                    </div>
                    <div class="list-group-item">
                        <strong>🔄 ترانشیپمنت</strong>
                        <small class="d-block text-muted">توزیع چندمرحله‌ای با گره‌های واسط</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// متن‌های نمونه
const sampleTexts = {
    1: 'یک شرکت پخش مواد غذایی دارای ۳ کارخانه تولید در شهرهای تهران، اصفهان و شیراز است که ظرفیت عرضه آن‌ها به ترتیب ۲۰۰، ۱۵۰ و ۱۸۰ تن در روز می‌باشد. این شرکت باید محصولات خود را به ۴ انبار توزیع در مشهد، تبریز، اهواز و رشت برساند که تقاضای آن‌ها به ترتیب ۲۰، ۱۰، ۱۴۰ و ۷۰ تن است. هزینه حمل هر تن کالا از هر کارخانه به هر انبار در جدول هزینه‌ها مشخص شده است. هدف مدیر شرکت کمینه‌سازی کل هزینه حمل و نقل با رعایت محدودیت‌های ظرفیت عرضه و تقاضا است.',
    
    2: 'یک کارخانه تولیدی ۴ اپراتور ماهر (علی، رضا، محمد، حسین) دارد که باید به صورت یک به یک به ۴ دستگاه مختلف (CNC، پرس، جوش و بسته‌بندی) اختصاص یابند. زمان انجام کار هر کارگر روی هر دستگاه (به دقیقه) در جدول زمان‌بندی مشخص است. از آنجا که هر کارگر فقط می‌تواند یک وظیفه را انجام دهد و هر دستگاه فقط یک اپراتور نیاز دارد، هدف ما تخصیص بهینه نیروها به ماشین‌آلات به گونه‌ای است که مجموع زمان‌های انجام کار کمینه شود.',
    
    3: 'یک شرکت پخش می‌خواهد کوتاه‌ترین مسیر ممکن را از شهر تهران به بندرعباس پیدا کند. شهرهای میانی در این شبکه جاده‌ای شامل قم، اصفهان، یزد، کرمان و شیراز هستند. فاصله بین شهرها (به کیلومتر) به صورت یال‌های شبکه مشخص است: تهران به قم ۱۲۵، تهران به اصفهان ۵۰، قم به اصفهان ۳۳۰، اصفهان به یزد ۳۲۰، یزد به کرمان ۳۷۰ و کرمان به بندرعباس ۴۱۰ کیلومتر است. هدف یافتن مسیری با کمترین فاصله کلی از مبدأ به مقصد است.',
    
    4: 'یک کارخانه مبل‌سازی دو محصول تولید می‌کند: مبل راحتی با سود ۵۰۰ هزار تومان و مبل کلاسیک با سود ۷۰۰ هزار تومان. هر مبل راحتی نیاز به ۲ ساعت نجاری و ۱ ساعت رنگ‌آمیزی دارد. هر مبل کلاسیک نیاز به ۳ ساعت نجاری و ۲ ساعت رنگ‌آمیزی دارد. ظرفیت هفتگی کارگاه نجاری ۶ ساعت و کارگاه رنگ‌آمیزی ۴۰ ساعت است. هدف شرکت بیشینه‌سازی سود هفتگی با رعایت محدودیت منابع و قیود تولید است.'
};

// متغیر سراسری برای نگهداری داده‌های مدل استخراج‌شده
let suggestedModelData = null;

function loadSampleText(id) {
    document.getElementById('problemText').value = sampleTexts[id];
}

async function analyzeProblem() {
    const text = document.getElementById('problemText').value.trim();
    const btn = document.getElementById('analyzeBtn');
    const resultDiv = document.getElementById('analysisResult');
    
    if (!text) {
        alert('لطفاً ابتدا مسئله خود را توصیف کنید.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال تحلیل...';
    resultDiv.classList.add('d-none');

    try {
        const analyzeUrl = '<?= or_url("controller=smart_modeler&action=analyze") ?>';
        
        const response = await fetch(analyzeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ text: text })
        });

        if (!response.ok) {
            throw new Error(`HTTP Error: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            displayResults(data);
        } else {
            alert('❌ خطا: ' + data.error);
        }

    } catch (error) {
        console.error('SmartModeler Error:', error);
        alert(' خطا در تحلیل: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-magic"></i> تحلیل هوشمند';
    }
}

function displayResults(data) {
    const resultDiv = document.getElementById('analysisResult');
    resultDiv.classList.remove('d-none');

    const confidenceColor = data.confidence >= 70 ? 'success' : (data.confidence >= 50 ? 'warning' : 'danger');
    
    const typeNames = {
        'TRANS': 'حمل و نقل',
        'ASSIGN': 'تخصیص',
        'SHORTEST': 'کوتاه‌ترین مسیر',
        'LP': 'برنامه‌ریزی خطی',
        'TRANSSHIP': 'ترانشیپمنت'
    };

    // ذخیره داده‌های مدل برای استفاده در ایجاد پروژه
    suggestedModelData = data.extracted_params?.model_data || null;

    let html = `
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-${confidenceColor} text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle"></i> نتیجه تحلیل هوشمند
                    <span class="badge bg-white text-${confidenceColor} ms-2">
                        اطمینان: ${data.confidence}%
                    </span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-${confidenceColor} mb-3">
                    <h6 class="mb-1"><i class="fas fa-bullseye"></i> نوع مسئله تشخیص‌داده‌شده:</h6>
                    <h4 class="mb-0">${data.detected_type_name || typeNames[data.detected_type] || data.detected_type}</h4>
                </div>
    `;

    // نمایش امتیازها به درصد
    if (data.all_scores && typeof data.all_scores === 'object' && Object.keys(data.all_scores).length > 0) {
        const scores = data.all_scores;
        const totalScore = Object.values(scores).reduce((sum, score) => sum + (parseFloat(score) || 0), 0);
        
        html += `
            <h6 class="mb-2"><i class="fas fa-chart-bar text-primary"></i> امتیاز همه انواع مسائل:</h6>
            <div class="mb-3">
        `;
        
        for (const [type, score] of Object.entries(scores)) {
            const scoreNum = parseFloat(score) || 0;
            const percent = totalScore > 0 ? (scoreNum / totalScore) * 100 : 0;
            const typeName = typeNames[type] || type;
            const isActive = type === data.detected_type;
            const barColor = isActive ? 'primary' : 'secondary';
            
            html += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold ${isActive ? 'text-primary' : 'text-muted'}">
                            ${isActive ? '🎯 ' : ''}${typeName}
                        </span>
                        <span class="badge bg-${barColor} bg-opacity-10 text-${barColor}">
                            ${percent.toFixed(1)}%
                        </span>
                    </div>
                    <div class="progress" style="height: 8px; background-color: #e9ecef;">
                        <div class="progress-bar bg-${barColor}" 
                             style="width: ${percent}%; transition: width 0.3s ease;"></div>
                    </div>
                </div>
            `;
        }
        
        html += `</div>`;
    }

    // مدل ریاضی
    if (data.math_model) {
        html += `
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-square-root-alt text-danger"></i> ${data.math_model.title}</h6>
                    <div class="mb-2">
                        <strong>متغیرها:</strong>
                        <p class="mb-0 small">${data.math_model.variables}</p>
                    </div>
                    <div class="mb-2">
                        <strong>تابع هدف:</strong>
                        <code class="d-block bg-white p-2 rounded">${data.math_model.objective}</code>
                    </div>
                    <div class="mb-2">
                        <strong>قیود:</strong>
                        <ul class="mb-0 small">
                            ${data.math_model.constraints.map(c => `<li>${c}</li>`).join('')}
                        </ul>
                    </div>
                    <p class="mb-0 small text-muted"><i class="fas fa-info-circle"></i> ${data.math_model.explanation}</p>
                </div>
            </div>
        `;
    }

    // روش حل پیشنهادی
    if (data.suggested_method) {
        html += `
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-cogs text-success"></i> روش حل پیشنهادی:</h6>
                    <div class="mb-2">
                        <strong>روش اصلی:</strong> ${data.suggested_method.primary.name}
                        <small class="d-block text-muted">${data.suggested_method.primary.reason}</small>
                    </div>
                    ${data.suggested_method.alternative ? `
                        <div>
                            <strong>روش جایگزین:</strong> ${data.suggested_method.alternative.name}
                            <small class="d-block text-muted">${data.suggested_method.alternative.reason}</small>
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }

    // هشدارها
    if (data.warnings && data.warnings.length > 0) {
        html += `
            <div class="alert alert-warning mb-3">
                <h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> هشدارها:</h6>
                <ul class="mb-0 small">
                    ${data.warnings.map(w => `<li>${w}</li>`).join('')}
                </ul>
            </div>
        `;
    }

    // مراحل بعدی
    if (data.next_steps && data.next_steps.length > 0) {
        html += `
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-list-ol text-primary"></i> مراحل بعدی:</h6>
                    <ol class="mb-0 small">
                        ${data.next_steps.map(s => `<li class="mb-1">${s}</li>`).join('')}
                    </ol>
                </div>
            </div>
        `;
    }

    html += `
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="createFromAnalysis('${data.detected_type}')">
                        <i class="fas fa-plus-circle"></i> ایجاد پروژه با این مشخصات
                    </button>
                </div>
            </div>
        </div>
    `;

    resultDiv.innerHTML = html;
    resultDiv.scrollIntoView({behavior: 'smooth'});
}

function clearAll() {
    document.getElementById('problemText').value = '';
    document.getElementById('analysisResult').classList.add('d-none');
    document.getElementById('analysisResult').innerHTML = '';
}

function createFromAnalysis(type) {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال پیاده‌سازی خودکار مدل...';

    // استفاده از داده‌های استخراج‌شده هوشمند، یا در صورت نبود، یک ساختار پیش‌فرض
    const payloadData = suggestedModelData || {
        name: `پروژه ${type} - ایجاد شده توسط مدلسازی هوشمند`,
        description: document.getElementById('problemText').value.trim(),
        objective: 'minimize'
    };

    fetch('<?= or_url("controller=smart_modeler&action=createProject") ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            type: type,
            model_data: payloadData
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            window.location.href = data.redirect;
        } else {
            alert('❌ خطا: ' + data.error);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-plus-circle"></i> ایجاد پروژه با این مشخصات';
        }
    })
    .catch(err => {
        alert('❌ خطای شبکه: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus-circle"></i> ایجاد پروژه با این مشخصات';
    });
}
</script>