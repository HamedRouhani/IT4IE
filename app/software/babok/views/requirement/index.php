<?php
/**
 * ویو استخراج و تحلیل نیازمندی
 * مسیر: app/software/babok/views/requirement/index.php
 */
$pageTitle = 'استخراج و تحلیل نیازمندی - BABOK Analyzer';
$activePage = 'requirement';
?>

<!-- هدر -->
<div class="requirement-analyzer">
    <h2><i class="fas fa-robot"></i> استخراج و تحلیل یکپارچه نیازمندی</h2>
    <p>
        متن نیازمندی خود را وارد کنید یا با استفاده از میکروفون ضبط کنید. 
        سیستم به صورت هوشمند نیازمندی‌ها را استخراج کرده و تکنیک‌های مناسب BABOK را پیشنهاد می‌دهد.
    </p>
</div>

<!-- فرم ورودی -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-pencil-alt"></i> ورود متن نیازمندی
        </div>
    </div>

    <!-- 📝 نمونه‌های متن آماده برای تست سریع -->
    <div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
        <h4 class="card-title" style="margin-bottom: 15px;">
            <i class="fas fa-lightbulb" style="color: #f39c12;"></i> 
            نمونه‌های آماده برای تست سریع
        </h4>
        <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 15px;">
            روی هر نمونه کلیک کنید تا متن به صورت خودکار در کادر بالا قرار گیرد:
        </p>
        
        <div class="sample-texts-grid">
            <button type="button" class="sample-text-btn" data-sample="financial">
                <div class="sample-icon">💰</div>
                <div class="sample-info">
                    <strong>سیستم مدیریت مالی</strong>
                    <small>فاکتور، حسابداری، گزارش‌های مالی</small>
                </div>
            </button>
            
            <button type="button" class="sample-text-btn" data-sample="production">
                <div class="sample-icon">🏭</div>
                <div class="sample-info">
                    <strong>سیستم مدیریت تولید</strong>
                    <small>انبار، مواد اولیه، کنترل کیفیت</small>
                </div>
            </button>
            
            <button type="button" class="sample-text-btn" data-sample="service">
                <div class="sample-icon">🎯</div>
                <div class="sample-info">
                    <strong>سیستم خدمات و پشتیبانی</strong>
                    <small>تیکت، مشتریان، رضایت‌سنجی</small>
                </div>
            </button>
            
            <button type="button" class="sample-text-btn" data-sample="health">
                <div class="sample-icon">🏥</div>
                <div class="sample-info">
                    <strong>سیستم اطلاعات سلامت</strong>
                    <small>بیماران، نسخه، سوابق پزشکی</small>
                </div>
            </button>
            
            <button type="button" class="sample-text-btn" data-sample="hr">
                <div class="sample-icon">👥</div>
                <div class="sample-info">
                    <strong>سیستم منابع انسانی</strong>
                    <small>کارکنان، حقوق، ارزیابی عملکرد</small>
                </div>
            </button>
            
            <button type="button" class="sample-text-btn" data-sample="education">
                <div class="sample-icon">🎓</div>
                <div class="sample-info">
                    <strong>سیستم مدیریت آموزشی</strong>
                    <small>دانشجویان، کلاس‌ها، نمرات</small>
                </div>
            </button>
        </div>
    </div>

    <style>
    .sample-texts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 12px;
    }

    .sample-text-btn {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: #fff;
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: right;
        font-family: inherit;
        width: 100%;
    }

    .sample-text-btn:hover {
        border-color: #3498db;
        background: #f0f7ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.15);
    }

    .sample-text-btn:active {
        transform: translateY(0);
    }

    .sample-icon {
        font-size: 1.8rem;
        flex-shrink: 0;
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #667eea15, #764ba215);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sample-info {
        flex: 1;
        min-width: 0;
    }

    .sample-info strong {
        display: block;
        color: #2c3e50;
        font-size: 0.95rem;
        margin-bottom: 3px;
    }

    .sample-info small {
        color: #7f8c8d;
        font-size: 0.78rem;
        line-height: 1.4;
    }

    @media (max-width: 576px) {
        .sample-texts-grid {
            grid-template-columns: 1fr;
        }
        .sample-text-btn {
            padding: 12px;
        }
        .sample-icon {
            width: 40px;
            height: 40px;
            font-size: 1.5rem;
        }
    }
    </style>
    
    <div class="form-group">
        <label class="form-label" for="requirementText">متن نیازمندی</label>
        <textarea id="requirementText" class="form-control requirement-textarea" rows="10"
                  placeholder="مثال: ما نیاز به سیستم مدیریت مالی داریم که بتواند فاکتورها را مدیریت کند، گزارش‌های مالی تولید کند و حسابرسی را تسهیل کند. کاربران باید بتوانند تراکنش‌ها را ثبت و پیگیری کنند..."></textarea>
        <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 0.8rem; color: #999;">
            <span>حداقل ۱۰ کاراکتر، حداکثر ۱۰۰۰۰ کاراکتر</span>
            <span id="charCount">0 کاراکتر</span>
        </div>
    </div>
    
    <!-- ضبط صدا -->
    <div class="voice-recorder">
        <button type="button" id="startVoiceBtn" class="voice-btn voice-start">
            <i class="fas fa-microphone"></i>
            <span>ضبط صدا</span>
        </button>
        
        <button type="button" id="stopVoiceBtn" class="voice-btn voice-stop" style="display:none;">
            <i class="fas fa-stop"></i>
            <span>توقف</span>
        </button>
        
        <div class="voice-info">
            <div class="voice-title">
                <i class="fas fa-microphone-alt"></i> ضبط صوتی نیازمندی
            </div>
            <div class="voice-status" id="voiceStatus">
                برای شروع، روی دکمه «ضبط صدا» کلیک کنید
            </div>
        </div>
        
        <div class="voice-timer" id="recordingTimer">00:00</div>
    </div>

    <!-- انیمیشن موج صدا -->
    <div class="voice-wave" id="voiceWave">
        <span></span><span></span><span></span><span></span><span></span><span></span><span></span>
    </div>
    
    <div class="d-flex gap-2 mt-3">
        <button id="analyzeBtn" class="btn btn-primary btn-lg">
            <i class="fas fa-search-plus"></i> تحلیل نیازمندی
        </button>
        <button id="clearBtn" class="btn btn-secondary btn-lg">
            <i class="fas fa-eraser"></i> پاک کردن
        </button>
    </div>
</div>

<!-- ناحیه بارگذاری -->
<div id="loadingSection" class="card" style="display: none; text-align: center; padding: 50px;">
    <div class="spinner-border" style="width: 4rem; height: 4rem; color: var(--soft-secondary);"></div>
    <h3 style="margin-top: 20px;">در حال تحلیل نیازمندی...</h3>
    <p class="text-muted">لطفاً صبر کنید. سیستم در حال استخراج نیازمندی‌ها و پیشنهاد تکنیک‌ها است.</p>
</div>

<!-- نتایج -->
<div id="resultsSection" style="display: none;"></div>

<!-- راهنما -->
<div class="card" style="margin-top: 20px; background: #f8f9fa;">
    <h4 class="card-title"><i class="fas fa-question-circle"></i> راهنمای استفاده</h4>
    <div class="row" style="margin-top: 15px;">
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.95rem;">📝 ورود متن</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                متن نیازمندی خود را به صورت طبیعی بنویسید. هر چه متن دقیق‌تر باشد، نتایج بهتری دریافت می‌کنید.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.95rem;">🎤 ضبط صوتی</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                با کلیک روی دکمه میکروفون، نیازمندی را به صورت صوتی بیان کنید. متن به صورت خودکار استخراج می‌شود.
            </p>
        </div>
        <div class="card" style="margin-bottom: 0;">
            <h5 style="font-size: 0.95rem;">🤖 تحلیل هوشمند</h5>
            <p class="text-muted" style="font-size: 0.85rem;">
                سیستم با استفاده از الگوریتم‌های پردازش متن فارسی، نیازمندی‌ها را استخراج و تکنیک‌های BABOK را پیشنهاد می‌دهد.
            </p>
        </div>
    </div>
</div>

<script>
// ============================================
// متغیرهای عمومی
// ============================================
const charCount = document.getElementById('charCount');
const requirementText = document.getElementById('requirementText');
const analyzeBtn = document.getElementById('analyzeBtn');
const clearBtn = document.getElementById('clearBtn');
const loadingSection = document.getElementById('loadingSection');
const resultsSection = document.getElementById('resultsSection');

function updateCharCount() {
    charCount.textContent = requirementText.value.length + ' کاراکتر';
}
requirementText.addEventListener('input', updateCharCount);

clearBtn.addEventListener('click', function () {
    requirementText.value = '';
    updateCharCount();
    resultsSection.style.display = 'none';
    resultsSection.innerHTML = '';
});

// ============================================
// 🎤 کلاس SpeechRecognizer (مطابق با کد اصلی BABOK)
// ============================================
class SpeechRecognizer {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.finalTranscript = '';
        this.lastFinalText = '';
        this.interimTranscript = '';
        this.lastUpdateTime = 0;
        this.updateDelay = 300;
        this.onResultCallback = null;
        this.onEndCallback = null;
        this.onErrorCallback = null;
        this.timerInterval = null;
        this.seconds = 0;
        
        this.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.isSupported = !!this.SpeechRecognition;
        
        // 🔑 تشخیص موبایل برای تنظیمات متفاوت
        this.isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    start(onResult, onEnd, onError) {
        if (!this.isSupported) {
            if (onError) onError('مرورگر شما پشتیبانی نمی‌کند. از Chrome یا Edge استفاده کنید.');
            return false;
        }
        if (this.isListening) this.stop();

        this.recognition = new this.SpeechRecognition();
        this.recognition.lang = 'fa-IR';
        this.recognition.continuous = true;

        // 🔑 تنظیمات متفاوت برای موبایل (کلید رفع تکرار!)
        if (this.isMobile) {
            this.recognition.interimResults = false;  // ⭐ بدون متن موقت
            this.recognition.maxAlternatives = 1;
            this.updateDelay = 500;  // تاخیر بیشتر
        } else {
            this.recognition.interimResults = true;
            this.recognition.maxAlternatives = 3;
            this.updateDelay = 200;
        }

        this.onResultCallback = onResult;
        this.onEndCallback = onEnd;
        this.onErrorCallback = onError;

        this.recognition.onstart = () => this._handleStart();
        this.recognition.onresult = (event) => this._handleResult(event);
        this.recognition.onerror = (event) => this._handleError(event);
        this.recognition.onend = () => this._handleEnd();

        try {
            this.recognition.start();
            return true;
        } catch (e) {
            if (onError) onError('خطا: ' + e.message);
            return false;
        }
    }

    stop() {
        if (this.recognition && this.isListening) {
            try { this.recognition.stop(); } catch (e) {}
        }
        this._cleanup();
    }

    clear() {
        this.finalTranscript = '';
        this.lastFinalText = '';
        this.interimTranscript = '';
        this.seconds = 0;
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }

    isActive() { return this.isListening; }

    _handleStart() {
        this.isListening = true;
        this.seconds = 0;
        this.finalTranscript = '';
        this.lastFinalText = '';
        this.interimTranscript = '';

        if (this.timerInterval) clearInterval(this.timerInterval);
        this.timerInterval = setInterval(() => {
            this.seconds++;
            const m = String(Math.floor(this.seconds / 60)).padStart(2, '0');
            const s = String(this.seconds % 60).padStart(2, '0');
            const el = document.getElementById('recordingTimer');
            if (el) el.textContent = `${m}:${s}`;
        }, 1000);
    }

    _handleResult(event) {
        // 🔑 Rate limiting برای جلوگیری از فراخوانی بیش از حد
        const now = Date.now();
        if (now - this.lastUpdateTime < this.updateDelay) return;
        this.lastUpdateTime = now;

        let finalText = '';
        let interimText = '';

        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalText += transcript + ' ';
            } else {
                interimText += transcript;
            }
        }

        if (finalText) {
            const trimmedFinal = finalText.trim();
            // 🔑 فقط اگر متن جدید با قبلی متفاوت بود اضافه شود (ضد تکرار)
            if (trimmedFinal !== this.lastFinalText) {
                this.finalTranscript += finalText;
                this.lastFinalText = trimmedFinal;
            }
            this.interimTranscript = '';
        } else if (this.isMobile) {
            // 🔑 در موبایل متن موقت را نادیده می‌گیریم
            return;
        } else {
            this.interimTranscript = interimText;
        }

        const fullText = this.finalTranscript + this.interimTranscript;
        const trimmedFull = fullText.trim();

        if (trimmedFull.length > 0 && this.onResultCallback) {
            this.onResultCallback({
                final: this.finalTranscript.trim(),
                interim: this.interimTranscript,
                full: trimmedFull,
                isFinal: !!finalText
            });
        }
    }

    _handleError(event) {
        let msg = 'خطا در تشخیص گفتار';
        switch (event.error) {
            case 'not-allowed':
                msg = '❌ دسترسی به میکروفون مجاز نیست.';
                break;
            case 'no-speech':
                msg = '⚠️ صدایی تشخیص داده نشد.';
                break;
            case 'audio-capture':
                msg = '❌ میکروفون پیدا نشد.';
                break;
            case 'network':
                msg = '❌ خطای شبکه - اتصال اینترنت را بررسی کنید.';
                break;
            default:
                msg = `❌ خطا: ${event.error}`;
        }
        if (this.onErrorCallback) this.onErrorCallback(msg, event.error);
        if (['not-allowed', 'audio-capture'].includes(event.error)) {
            this.stop();
        }
    }

    _handleEnd() {
        this._cleanup();
        if (this.onEndCallback) {
            this.onEndCallback({
                final: this.finalTranscript.trim(),
                hasText: this.finalTranscript.trim().length > 0
            });
        }
    }

    _cleanup() {
        this.isListening = false;
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
            this.timerInterval = null;
        }
    }
}

// ============================================
// کنترل ضبط صدا (بر اساس کلاس بالا)
// ============================================
let recognizer = null;
let isRecording = false;

const startVoiceBtn = document.getElementById('startVoiceBtn');
const stopVoiceBtn = document.getElementById('stopVoiceBtn');
const voiceStatus = document.getElementById('voiceStatus');
const recordingTimer = document.getElementById('recordingTimer');
const voiceWave = document.getElementById('voiceWave');

// ذخیره متن پایه (قبل از شروع ضبط)
let baseText = '';

function startRecording() {
    if (recognizer) {
        recognizer.stop();
        recognizer = null;
    }

    // 📸 عکس از متن فعلی قبل از شروع
    baseText = requirementText.value.trim();
    if (baseText) baseText += ' ';

    recognizer = new SpeechRecognizer();

    const started = recognizer.start(
        // onResult
        (result) => {
            requirementText.value = baseText + result.full;
            updateCharCount();
        },
        // onEnd
        (data) => {
            isRecording = false;
            startVoiceBtn.style.display = 'inline-flex';
            stopVoiceBtn.style.display = 'none';
            recordingTimer.classList.remove('active');
            voiceWave.classList.remove('active');

            if (data.hasText) {
                voiceStatus.textContent = '✅ ضبط کامل شد. متن قابل ویرایش است.';
                updateCharCount();
            } else {
                voiceStatus.textContent = '⚠️ متنی تشخیص داده نشد. دوباره تلاش کنید.';
            }
        },
        // onError
        (message) => {
            isRecording = false;
            startVoiceBtn.style.display = 'inline-flex';
            stopVoiceBtn.style.display = 'none';
            recordingTimer.classList.remove('active');
            voiceWave.classList.remove('active');
            voiceStatus.textContent = message;
        }
    );

    if (started) {
        isRecording = true;
        startVoiceBtn.style.display = 'none';
        stopVoiceBtn.style.display = 'inline-flex';
        recordingTimer.classList.add('active');
        voiceWave.classList.add('active');
        voiceStatus.textContent = recognizer.isMobile 
            ? '🎤 در حال گوش دادن (موبایل)... شمرده صحبت کنید'
            : '🎤 در حال گوش دادن... شمرده صحبت کنید';
    }
}

function stopRecording() {
    if (recognizer && recognizer.isActive()) {
        recognizer.stop();
        isRecording = false;
        startVoiceBtn.style.display = 'inline-flex';
        stopVoiceBtn.style.display = 'none';
        recordingTimer.classList.remove('active');
        voiceWave.classList.remove('active');
        voiceStatus.textContent = '⏹️ ضبط متوقف شد';
    }
}

startVoiceBtn.addEventListener('click', startRecording);
stopVoiceBtn.addEventListener('click', stopRecording);

// ============================================
// 🤖 تحلیل نیازمندی
// ============================================
analyzeBtn.addEventListener('click', function () {
    const text = requirementText.value.trim();
    if (text.length < 10) {
        alert('لطفاً حداقل ۱۰ کاراکتر متن وارد کنید.');
        return;
    }
    if (text.length > 10000) {
        alert('متن نباید بیشتر از ۱۰۰۰۰ کاراکتر باشد.');
        return;
    }

    loadingSection.style.display = 'block';
    resultsSection.style.display = 'none';
    analyzeBtn.disabled = true;

    fetch('?route=requirement_analyze', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ text: text })
    })
    .then(res => res.json())
    .then(data => {
        loadingSection.style.display = 'none';
        analyzeBtn.disabled = false;
        if (data.success) {
            renderResults(data.data);
        } else {
            alert(data.error || 'خطا در تحلیل نیازمندی');
        }
    })
    .catch(err => {
        loadingSection.style.display = 'none';
        analyzeBtn.disabled = false;
        console.error('Error:', err);
        alert('خطا در ارتباط با سرور');
    });
});

// ============================================
// نمایش نتایج (بدون تغییر)
// ============================================
function renderResults(data) {
    let html = '';

    html += `
        <div class="card">
            <h3 class="card-title"><i class="fas fa-chart-bar"></i> خلاصه تحلیل</h3>
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));">
                <div style="text-align:center; padding:15px; background:#e8f4fd; border-radius:8px;">
                    <div style="font-size:2rem; font-weight:700; color:#3498db;">${data.stats.total}</div>
                    <div class="text-muted">کل نیازمندی‌ها</div>
                </div>
                <div style="text-align:center; padding:15px; background:#d4edda; border-radius:8px;">
                    <div style="font-size:2rem; font-weight:700; color:#27ae60;">${data.stats.functional}</div>
                    <div class="text-muted">عملکردی</div>
                </div>
                <div style="text-align:center; padding:15px; background:#fff3cd; border-radius:8px;">
                    <div style="font-size:2rem; font-weight:700; color:#f39c12;">${data.stats.non_functional}</div>
                    <div class="text-muted">غیرعملکردی</div>
                </div>
                <div style="text-align:center; padding:15px; background:#f8d7da; border-radius:8px;">
                    <div style="font-size:2rem; font-weight:700; color:#e74c3c;">${data.stats.techniques}</div>
                    <div class="text-muted">تکنیک پیشنهادی</div>
                </div>
            </div>
            <p style="margin-top:15px;"><strong>حوزه کاری:</strong> <span class="badge badge-primary">${data.domain}</span></p>
        </div>
    `;

    if (data.suggestion) {
        html += `
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff;">
                <h3 style="margin-bottom:10px;"><i class="fas fa-lightbulb"></i> پیشنهاد سیستم</h3>
                <h4 style="margin:0 0 8px 0;">${data.suggestion.title}</h4>
                <p style="margin:0; opacity:0.9;">${data.suggestion.description}</p>
            </div>
        `;
    }

    html += `<div class="card"><h3 class="card-title"><i class="fas fa-list-check"></i> نیازمندی‌های استخراج‌شده</h3>`;

    if (data.requirements.functional.length > 0) {
        html += `<h4 style="margin:15px 0 10px 0; color:#3498db;">نیازمندی‌های عملکردی</h4>`;
        data.requirements.functional.forEach(req => {
            html += `
                <div class="requirement-item functional">
                    <h4>${req.title}</h4>
                    <p>${req.description || ''}</p>
                    ${req.babok_reference ? `<div class="requirement-meta"><i class="fas fa-book"></i> ${req.babok_reference}</div>` : ''}
                </div>
            `;
        });
    }

    if (data.requirements.non_functional.length > 0) {
        html += `<h4 style="margin:15px 0 10px 0; color:#f39c12;">نیازمندی‌های غیرعملکردی</h4>`;
        data.requirements.non_functional.forEach(req => {
            html += `
                <div class="requirement-item non-functional">
                    <h4>${req.title}</h4>
                    <p>${req.description || ''}</p>
                </div>
            `;
        });
    }
    html += `</div>`;

    if (data.techniques.length > 0) {
        html += `<div class="card"><h3 class="card-title"><i class="fas fa-tools"></i> تکنیک‌های پیشنهادی BABOK</h3>`;
        data.techniques.forEach((item, index) => {
            const tech = item.technique;
            html += `
                <div class="technique-card" style="border-right: 4px solid ${index < 3 ? '#27ae60' : '#3498db'};">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                        <div style="flex:1;">
                            <span class="badge badge-${index < 3 ? 'success' : 'primary'}">پیشنهاد #${index + 1}</span>
                            <h4 style="margin:8px 0 5px 0;">${tech.name}</h4>
                            <p style="font-size:0.85rem; color:#666;">${tech.purpose || ''}</p>
                            ${item.reason ? `<div class="technique-reason"><i class="fas fa-info-circle"></i> ${item.reason}</div>` : ''}
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.8rem; font-weight:700; color:#3498db;">${item.score_percent}%</div>
                            <div class="text-muted" style="font-size:0.7rem;">تطابق</div>
                        </div>
                    </div>
                    <a href="?route=techniques_view&id=${tech.id}" class="btn btn-sm btn-primary" style="margin-top:10px;">
                        <i class="fas fa-eye"></i> مشاهده جزئیات
                    </a>
                </div>
            `;
        });
        html += `</div>`;
    }

    resultsSection.innerHTML = html;
    resultsSection.style.display = 'block';
    resultsSection.scrollIntoView({ behavior: 'smooth' });
}

// ============================================
// 📝 نمونه‌های متن آماده
// ============================================
const sampleTexts = {
    financial: `ما نیاز به یک سیستم مدیریت مالی جامع داریم که بتواند فاکتورها را به صورت خودکار صادر کند، تراکنش‌های مالی را ثبت و پیگیری کند و گزارش‌های مالی دقیق تولید کند. کاربران باید بتوانند حساب‌های بانکی را مدیریت کنند، بودجه‌بندی انجام دهند و وضعیت مالی شرکت را در لحظه مشاهده کنند. امنیت داده‌های مالی بسیار مهم است و باید رمزنگاری شوند. سیستم باید امکان حسابرسی داخلی و خارجی را فراهم کند و با استانداردهای حسابداری ایران سازگار باشد. مدیران باید بتوانند گزارش‌های تحلیلی از عملکرد مالی دریافت کنند.`,
    
    production: `سیستم مدیریت تولید نیاز داریم که بتواند خط تولید را مدیریت کند، موجودی مواد اولیه را کنترل کند و سفارش‌های مشتریان را پیگیری کند. کاربران باید بتوانند برنامه تولید را تنظیم کنند، کیفیت محصولات را بررسی کنند و ضایعات را ثبت کنند. سیستم باید امکان ردیابی محصولات از مواد اولیه تا محصول نهایی را فراهم کند. گزارش‌های عملکرد خط تولید و بهره‌وری ماشین‌آلات ضروری است. انبارداری و توزیع محصولات نیز باید در سیستم مدیریت شود.`,
    
    service: `ما به یک سیستم مدیریت خدمات و پشتیبانی نیاز داریم که بتواند درخواست‌های مشتریان را به صورت تیکت ثبت و پیگیری کند. کاربران باید بتوانند تیکت‌ها را به کارشناسان مربوطه ارجاع دهند، اولویت‌بندی کنند و وضعیت را به مشتریان اطلاع دهند. سیستم باید امکان نظرسنجی از مشتریان و اندازه‌گیری رضایت آن‌ها را فراهم کند. پایگاه دانش برای پاسخ به سوالات متداول نیاز است. مدیران باید گزارش‌های عملکرد تیم پشتیبانی را مشاهده کنند.`,
    
    health: `سیستم اطلاعات سلامت نیاز داریم که بتواند پرونده الکترونیک بیماران را مدیریت کند، سوابق پزشکی را ذخیره و بازیابی کند و نسخه‌های پزشکان را ثبت کند. کاربران باید بتوانند نوبت‌دهی را مدیریت کنند، نتایج آزمایش‌ها را ثبت کنند و با بیماران ارتباط برقرار کنند. امنیت و محرمانگی اطلاعات بیماران بسیار مهم است و باید با قوانین حفاظت از داده‌ها سازگار باشد. سیستم باید امکان صدور نسخه الکترونیک و ارتباط با بیمه‌ها را فراهم کند.`,
    
    hr: `ما به یک سیستم مدیریت منابع انسانی نیاز داریم که بتواند اطلاعات کارکنان را مدیریت کند، حقوق و دستمزد را محاسبه کند و ارزیابی عملکرد را انجام دهد. کاربران باید بتوانند مرخصی‌ها را ثبت و تأیید کنند، آموزش‌ها را برنامه‌ریزی کنند و فرآیند استخدام را مدیریت کنند. سیستم باید امکان تولید فیش حقوقی و گزارش‌های قانونی برای سازمان‌های مربوطه را فراهم کند. مدیران باید بتوانند شاخص‌های کلیدی منابع انسانی را مشاهده کنند.`,
    
    education: `سیستم مدیریت آموزشی نیاز داریم که بتواند اطلاعات دانشجویان را مدیریت کند، کلاس‌ها و دروس را برنامه‌ریزی کند و نمرات را ثبت کند. کاربران باید بتوانند در کلاس‌ها ثبت‌نام کنند، تکالیف را ارسال کنند و نمرات خود را مشاهده کنند. سیستم باید امکان برگزاری آزمون‌های آنلاین و تولید کارنامه را فراهم کند. اساتید باید بتوانند محتوای آموزشی را بارگذاری کنند و با دانشجویان ارتباط برقرار کنند. گزارش‌های آماری برای مدیران آموزشی ضروری است.`
};

// افزودن event listener برای دکمه‌های نمونه
document.querySelectorAll('.sample-text-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const sampleKey = this.dataset.sample;
        const text = sampleTexts[sampleKey];
        
        if (text) {
            requirementText.value = text;
            updateCharCount();
            
            // انیمیشن highlight کوتاه
            this.style.background = '#d4edda';
            setTimeout(() => {
                this.style.background = '#fff';
            }, 500);
            
            // اسکرول به textarea
            requirementText.scrollIntoView({ behavior: 'smooth', block: 'center' });
            requirementText.focus();
        }
    });
});
</script>