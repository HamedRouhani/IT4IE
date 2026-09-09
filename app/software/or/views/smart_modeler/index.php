<?php
/**
 * رابط کاربری مدلسازی هوشمند OR - نسخه ۲.۰
 * پشتیبانی از ۱۱ نوع مسئله
 */
?>

<div class="container-fluid py-4">
    <!-- هدر -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <div class="card-body p-4">
            <h2 class="mb-2">
                <i class="fas fa-brain"></i> مدلسازی هوشمند مسائل تحقیق در عملیات
                <span class="badge bg-white text-primary ms-2" style="font-size: 0.6em;">v2.0 - پشتیبانی از ۱۱ نوع مسئله</span>
            </h2>
            <p class="mb-0 opacity-90" style="color:white">
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
                            <!-- انواع کلاسیک -->
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText('transport')">
                                <i class="fas fa-truck"></i> حمل و نقل
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText('assignment')">
                                <i class="fas fa-users"></i> تخصیص
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText('shortest')">
                                <i class="fas fa-route"></i> کوتاه‌ترین مسیر
                            </button>
                            <button class="btn btn-sm btn-outline-primary" onclick="loadSampleText('lp')">
                                <i class="fas fa-chart-line"></i> برنامه‌ریزی خطی
                            </button>
                            <!-- انواع پیشرفته -->
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('queueing')">
                                <i class="fas fa-line-up"></i> صف
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('markov')">
                                <i class="fas fa-random"></i> مارکوف
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('game')">
                                <i class="fas fa-chess"></i> نظریه بازی‌ها
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('montecarlo')">
                                <i class="fas fa-dice"></i> مونت‌کارلو
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('ilp')">
                                <i class="fas fa-hashtag"></i> برنامه‌ریزی صحیح
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="loadSampleText('dual')">
                                <i class="fas fa-exchange-alt"></i> دوگان
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
                    <h6 class="mb-0"><i class="fas fa-question-circle text-success"></i> انواع مسائل پشتیبانی‌شده (۱۱ نوع)</h6>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item"><strong>🚚 حمل و نقل</strong><small class="d-block text-muted">توزیع کالا از چند مبدأ به چند مقصد</small></div>
                    <div class="list-group-item"><strong>👷 تخصیص</strong><small class="d-block text-muted">اختصاص یک‌به‌یک عوامل به وظایف</small></div>
                    <div class="list-group-item"><strong>🛣️ کوتاه‌ترین مسیر</strong><small class="d-block text-muted">یافتن بهینه‌ترین مسیر در شبکه</small></div>
                    <div class="list-group-item"><strong>📈 برنامه‌ریزی خطی</strong><small class="d-block text-muted">بهینه‌سازی با قیود خطی</small></div>
                    <div class="list-group-item"><strong>🔄 ترانشیپمنت</strong><small class="d-block text-muted">توزیع چندمرحله‌ای با گره‌های واسط</small></div>
                    <div class="list-group-item border-top border-2"><strong>⏳ صف (Queueing)</strong><small class="d-block text-muted">تحلیل سیستم‌های انتظار و صف</small></div>
                    <div class="list-group-item"><strong>🔀 زنجیره مارکوف</strong><small class="d-block text-muted">مدل‌سازی فرآیندهای تصادفی</small></div>
                    <div class="list-group-item"><strong>♟️ نظریه بازی‌ها</strong><small class="d-block text-muted">تحلیل رقابت و تعادل نش</small></div>
                    <div class="list-group-item"><strong>🎲 شبیه‌سازی مونت‌کارلو</strong><small class="d-block text-muted">تحلیل ریسک و عدم قطعیت</small></div>
                    <div class="list-group-item"><strong>🔢 برنامه‌ریزی صحیح</strong><small class="d-block text-muted">بهینه‌سازی با متغیرهای صحیح</small></div>
                    <div class="list-group-item"><strong>🔃 نظریه دوگان</strong><small class="d-block text-muted">تحلیل حساسیت و قیمت سایه‌ای</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════
// متن‌های نمونه برای هر ۱۱ نوع مسئله
// ═══════════════════════════════════════════════════════
const sampleTexts = {
    transport: 'یک شرکت پخش مواد غذایی دارای ۳ کارخانه تولید در شهرهای تهران، اصفهان و شیراز است که ظرفیت عرضه آن‌ها به ترتیب ۲۰۰، ۱۵۰ و ۱۸۰ تن در روز می‌باشد. این شرکت باید محصولات خود را به ۴ انبار توزیع در مشهد، تبریز، اهواز و رشت برساند که تقاضای آن‌ها به ترتیب ۲۰، ۱۰، ۱۴۰ و ۷۰ تن است. هزینه حمل هر تن کالا از هر کارخانه به هر انبار در جدول هزینه‌ها مشخص شده است. هدف مدیر شرکت کمینه‌سازی کل هزینه حمل و نقل با رعایت محدودیت‌های ظرفیت عرضه و تقاضا است.',
    
    assignment: 'یک کارخانه تولیدی ۴ اپراتور ماهر (علی، رضا، محمد، حسین) دارد که باید به صورت یک به یک به ۴ دستگاه مختلف (CNC، پرس، جوش و بسته‌بندی) اختصاص یابند. زمان انجام کار هر کارگر روی هر دستگاه (به دقیقه) در جدول زمان‌بندی مشخص است. از آنجا که هر کارگر فقط می‌تواند یک وظیفه را انجام دهد و هر دستگاه فقط یک اپراتور نیاز دارد، هدف ما تخصیص بهینه نیروها به ماشین‌آلات به گونه‌ای است که مجموع زمان‌های انجام کار کمینه شود.',
    
    shortest: 'یک شرکت پخش می‌خواهد کوتاه‌ترین مسیر ممکن را از شهر تهران به بندرعباس پیدا کند. شهرهای میانی در این شبکه جاده‌ای شامل قم، اصفهان، یزد، کرمان و شیراز هستند. فاصله بین شهرها (به کیلومتر) به صورت یال‌های شبکه مشخص است: تهران به قم ۱۲۵، تهران به اصفهان ۵۰، قم به اصفهان ۳۳۰، اصفهان به یزد ۳۲۰، یزد به کرمان ۳۷۰ و کرمان به بندرعباس ۴۱۰ کیلومتر است. هدف یافتن مسیری با کمترین فاصله کلی از مبدأ به مقصد است.',
    
    lp: 'یک کارخانه مبل‌سازی دو محصول تولید می‌کند: مبل راحتی با سود ۵۰۰ هزار تومان و مبل کلاسیک با سود ۷۰۰ هزار تومان. هر مبل راحتی نیاز به ۲ ساعت نجاری و ۱ ساعت رنگ‌آمیزی دارد. هر مبل کلاسیک نیاز به ۳ ساعت نجاری و ۲ ساعت رنگ‌آمیزی دارد. ظرفیت هفتگی کارگاه نجاری ۶ ساعت و کارگاه رنگ‌آمیزی ۴۰ ساعت است. هدف شرکت بیشینه‌سازی سود هفتگی با رعایت محدودیت منابع و قیود تولید است.',
    
    queueing: 'در یک شعبه بانک، ۳ صندوق خدمت‌رسانی وجود دارد. مشتریان با نرخ λ=۱۵ نفر در ساعت وارد بانک می‌شوند و هر صندوق با نرخ μ=۸ نفر در ساعت خدمت‌رسانی می‌کند. هدف تحلیل عملکرد این سیستم صف و محاسبه میانگین تعداد مشتریان در صف، میانگین زمان انتظار و درصد بهره‌وری صندوق‌ها است.',
    
    markov: 'یک دستگاه تولیدی دارای سه حالت سالم، نیمه‌خراب و خراب است. ماتریس انتقال بین حالت‌ها به صورت زیر است: اگر دستگاه سالم باشد، با احتمال ۰.۹ سالم می‌ماند، با احتمال ۰.۰۵ نیمه‌خراب و با احتمال ۰.۰۵ خراب می‌شود. اگر نیمه‌خراب باشد، با احتمال ۰.۰۵ سالم، با احتمال ۰.۹ نیمه‌خراب و با احتمال ۰.۰۵ خراب می‌شود. اگر خراب باشد، با احتمال ۰.۰۵ سالم، با احتمال ۰.۰۵ نیمه‌خراب و با احتمال ۰.۹ خراب می‌ماند. دستگاه در حال حاضر سالم است. می‌خواهیم وضعیت دستگاه را پس از ۵ گام بررسی کنیم.',
    
    game: 'دو شرکت رقیب در بازار تبلیغات، هر یک دو استراتژی دارند: تبلیغات گسترده و تبلیغات محدود. ماتریس پرداخت (سود) شرکت اول به صورت زیر است: اگر هر دو تبلیغات گسترده کنند، سود شرکت اول ۳ واحد است. اگر شرکت اول گسترده و شرکت دوم محدود، سود ۲- واحد. اگر شرکت اول محدود و شرکت دوم گسترده، سود ۱- واحد. اگر هر دو محدود، سود ۴ واحد. هدف یافتن تعادل نش و استراتژی بهینه برای هر بازیکن است.',
    
    montecarlo: 'هزینه کل یک پروژه ساختمانی از جمع سه متغیر تصادفی به دست می‌آید: هزینه مواد با توزیع نرمال با میانگین ۲۰۰ میلیون تومان و انحراف معیار ۲۰، هزینه نیروی کار با توزیع یکنواخت بین ۱۰۰ تا ۱۵۰ میلیون تومان، و هزینه سربار با توزیع نرمال با میانگین ۵۰ و انحراف معیار ۵ میلیون تومان. می‌خواهیم با ۱۰۰۰۰ تکرار شبیه‌سازی مونت‌کارلو، توزیع هزینه کل پروژه را محاسبه کنیم.',
    
    ilp: 'یک کارخانه می‌خواهد تعداد ماشین‌آلات جدید را برای خط تولید تعیین کند. دو نوع ماشین موجود است: نوع اول با سود ۱۰۰ واحد و نوع دوم با سود ۱۵۰ واحد. هر ماشین نوع اول به ۲ واحد فضا و ۳ واحد بودجه نیاز دارد. هر ماشین نوع دوم به ۴ واحد فضا و ۲ واحد بودجه نیاز دارد. فضای موجود ۲۰ واحد و بودجه موجود ۳۰ واحد است. تعداد ماشین‌ها باید عدد صحیح باشد. هدف بیشینه‌سازی سود کل است.',
    
    dual: 'یک کارخانه دو محصول تولید می‌کند. تابع هدف بیشینه‌سازی سود با ضرایب ۳ و ۵ است. محدودیت‌های تولید شامل دو منبع هستند: منبع اول با ضرایب ۲ و ۱ و ظرفیت ۸، منبع دوم با ضرایب ۱ و ۳ و ظرفیت ۹. هدف تبدیل این مسئله به فرم دوگان و تحلیل قیمت سایه‌ای منابع است.'
};

// متغیر سراسری برای نگهداری داده‌های مدل استخراج‌شده
let suggestedModelData = null;

function loadSampleText(key) {
    document.getElementById('problemText').value = sampleTexts[key];
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
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ text: text })
        });
        if (!response.ok) throw new Error(`HTTP Error: ${response.status}`);
        const data = await response.json();
        if (data.success) {
            displayResults(data);
        } else {
            alert('❌ خطا: ' + data.error);
        }
    } catch (error) {
        console.error('SmartModeler Error:', error);
        alert('❌ خطا در تحلیل: ' + error.message);
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
        'TRANS': 'حمل و نقل', 'ASSIGN': 'تخصیص', 'SHORTEST': 'کوتاه‌ترین مسیر',
        'LP': 'برنامه‌ریزی خطی', 'TRANSSHIP': 'ترانشیپمنت',
        'QUEUEING': 'صف (Queueing)', 'MARKOV': 'زنجیره مارکوف',
        'GAME_THEORY': 'نظریه بازی‌ها', 'MONTE_CARLO': 'شبیه‌سازی مونت‌کارلو',
        'ILP': 'برنامه‌ریزی صحیح', 'DUAL': 'نظریه دوگان'
    };

    suggestedModelData = data.extracted_params?.model_data || null;

    let html = `
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-${confidenceColor} text-white py-3">
                <h5 class="mb-0">
                    <i class="fas fa-check-circle"></i> نتیجه تحلیل هوشمند
                    <span class="badge bg-white text-${confidenceColor} ms-2">اطمینان: ${data.confidence}%</span>
                    <span class="badge bg-white text-${confidenceColor} ms-2">فاصله: ${data.margin || 0}%</span>
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-${confidenceColor} mb-3">
                    <h6 class="mb-1"><i class="fas fa-bullseye"></i> نوع مسئله تشخیص‌داده‌شده:</h6>
                    <h4 class="mb-0">${data.detected_type_name || typeNames[data.detected_type] || data.detected_type}</h4>
                </div>
    `;

    // نمایش امتیازها
    if (data.all_scores && Object.keys(data.all_scores).length > 0) {
        const scores = data.all_scores;
        const totalScore = Object.values(scores).reduce((sum, s) => sum + (parseFloat(s) || 0), 0);
        html += `<h6 class="mb-2"><i class="fas fa-chart-bar text-primary"></i> امتیاز همه انواع مسائل:</h6><div class="mb-3">`;
        for (const [type, score] of Object.entries(scores)) {
            const scoreNum = parseFloat(score) || 0;
            const percent = totalScore > 0 ? (scoreNum / totalScore) * 100 : 0;
            const typeName = typeNames[type] || type;
            const isActive = type === data.detected_type;
            const barColor = isActive ? 'primary' : 'secondary';
            html += `
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold ${isActive ? 'text-primary' : 'text-muted'}">${isActive ? '🎯 ' : ''}${typeName}</span>
                        <span class="badge bg-${barColor} bg-opacity-10 text-${barColor}">${percent.toFixed(1)}%</span>
                    </div>
                    <div class="progress" style="height: 8px; background-color: #e9ecef;">
                        <div class="progress-bar bg-${barColor}" style="width: ${percent}%;"></div>
                    </div>
                </div>`;
        }
        html += `</div>`;
    }

    // مدل ریاضی
    if (data.math_model) {
        html += `
            <div class="card bg-light mb-3"><div class="card-body">
                <h6 class="mb-2"><i class="fas fa-square-root-alt text-danger"></i> ${data.math_model.title}</h6>
                <div class="mb-2"><strong>متغیرها:</strong><p class="mb-0 small">${data.math_model.variables}</p></div>
                <div class="mb-2"><strong>تابع هدف:</strong><code class="d-block bg-white p-2 rounded">${data.math_model.objective}</code></div>
                <div class="mb-2"><strong>قیود:</strong><ul class="mb-0 small">${data.math_model.constraints.map(c => `<li>${c}</li>`).join('')}</ul></div>
            </div></div>`;
    }

    // روش حل پیشنهادی
    if (data.suggested_method) {
        html += `
            <div class="card bg-light mb-3"><div class="card-body">
                <h6 class="mb-2"><i class="fas fa-cogs text-success"></i> روش حل پیشنهادی:</h6>
                <div class="mb-2"><strong>روش اصلی:</strong> ${data.suggested_method.primary.name}
                    <small class="d-block text-muted">${data.suggested_method.primary.reason}</small></div>
                ${data.suggested_method.alternative ? `<div><strong>روش جایگزین:</strong> ${data.suggested_method.alternative.name}
                    <small class="d-block text-muted">${data.suggested_method.alternative.reason}</small></div>` : ''}
            </div></div>`;
    }

    // هشدارها
    if (data.warnings && data.warnings.length > 0) {
        html += `<div class="alert alert-warning mb-3"><h6 class="mb-2"><i class="fas fa-exclamation-triangle"></i> هشدارها:</h6>
            <ul class="mb-0 small">${data.warnings.map(w => `<li>${w}</li>`).join('')}</ul></div>`;
    }

    // مراحل بعدی
    if (data.next_steps && data.next_steps.length > 0) {
        html += `<div class="card bg-light mb-3"><div class="card-body">
            <h6 class="mb-2"><i class="fas fa-list-ol text-primary"></i> مراحل بعدی:</h6>
            <ol class="mb-0 small">${data.next_steps.map(s => `<li class="mb-1">${s}</li>`).join('')}</ol>
        </div></div>`;
    }

    html += `
                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" onclick="createFromAnalysis('${data.detected_type}')">
                        <i class="fas fa-plus-circle"></i> ایجاد پروژه با این مشخصات
                    </button>
                </div>
            </div></div>`;

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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال ایجاد پروژه...';

    const payloadData = suggestedModelData || {
        name: `پروژه ${type} - ایجاد شده توسط مدلسازی هوشمند`,
        description: document.getElementById('problemText').value.trim(),
        objective: 'minimize'
    };

    fetch('<?= or_url("controller=smart_modeler&action=createProject") ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ type: type, model_data: payloadData })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
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