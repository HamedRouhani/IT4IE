<?php
$pageTitle = 'استخراج و تحلیل نیازمندی';
$activePage = 'requirement';
ob_start();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-robot"></i>
                        استخراج و تحلیل نیازمندی
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">هوش مصنوعی</span>
                        <span class="badge badge-success">یکپارچه</span>
                    </div>
                </div>
                <div class="card-body">
                    
                    <!-- انتخاب حالت ورودی -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <button class="btn btn-outline-primary btn-lg active" id="modeTextBtn" onclick="switchMode('text')">
                                    <i class="fas fa-keyboard"></i> ورود دستی متن
                                </button>
                                <button class="btn btn-outline-success btn-lg" id="modeVoiceBtn" onclick="switchMode('voice')">
                                    <i class="fas fa-microphone"></i> ورود با صوت
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- حالت ۱: ورود دستی متن -->
                    <div id="textMode" class="mode-section">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-edit"></i> تایپ متن</h5>
                                        <div class="card-tools">
                                            <span class="badge badge-info">حداقل ۱۰ کاراکتر</span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <textarea 
                                            id="manualText" 
                                            class="form-control" 
                                            rows="6"
                                            style="direction: rtl; font-size: 15px; line-height: 2;"
                                            placeholder="متن نیازمندی خود را در اینجا تایپ کنید..."
                                            oninput="updateManualWordCount(this.value)"
                                        ></textarea>
                                        <div class="mt-2">
                                            <span class="badge badge-info" id="manualWordCount">تعداد کلمات: ۰</span>
                                            <button class="btn btn-sm btn-secondary float-left" onclick="clearManualText()">
                                                <i class="fas fa-trash"></i> پاک کردن
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- نمونه متن -->
                        <div class="row mt-2">
                            <div class="col-12">
                                <div class="alert alert-secondary">
                                    <i class="fas fa-lightbulb"></i>
                                    <strong>نمونه متن:</strong>
                                    <button class="btn btn-sm btn-outline-primary m-1" onclick="loadSample('payment')">پرداخت</button>
                                    <button class="btn btn-sm btn-outline-primary m-1" onclick="loadSample('user')">کاربر</button>
                                    <button class="btn btn-sm btn-outline-primary m-1" onclick="loadSample('report')">گزارش</button>
                                    <button class="btn btn-sm btn-outline-primary m-1" onclick="loadSample('security')">امنیت</button>
                                    <button class="btn btn-sm btn-outline-primary m-1" onclick="loadSample('relationship')">روابط</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- حالت ۲: ورود با صوت -->
                    <div id="voiceMode" class="mode-section" style="display: none;">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-microphone"></i> ضبط صوت</h5>
                                        <div class="card-tools">
                                            <span class="badge badge-danger">🔴 زنده</span>
                                        </div>
                                    </div>
                                    <div class="card-body text-center">
                                        <div id="recordingStatus" class="mb-3">
                                            <span class="badge badge-secondary" id="statusText">آماده برای ضبط</span>
                                        </div>
                                        <div id="recordingTimer" style="font-size: 24px; font-weight: bold; color: #667eea; display: none;">
                                            ۰۰:۰۰
                                        </div>
                                        <div class="btn-group" role="group" style="gap: 10px;">
                                            <button 
                                                id="startRecordBtn" 
                                                class="btn btn-danger btn-lg" 
                                                onclick="startRecording()"
                                                style="border-radius: 50px; padding: 15px 35px;"
                                            >
                                                <i class="fas fa-circle"></i> شروع ضبط
                                            </button>
                                            <button 
                                                id="stopRecordBtn" 
                                                class="btn btn-secondary btn-lg" 
                                                onclick="stopRecording()" 
                                                disabled
                                                style="border-radius: 50px; padding: 15px 35px;"
                                            >
                                                <i class="fas fa-stop"></i> توقف
                                            </button>
                                        </div>
                                        <div id="recordingWave" style="display: none; margin-top: 20px;">
                                            <div class="wave">
                                                <span></span><span></span><span></span>
                                                <span></span><span></span><span></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-file-alt"></i> متن تشخیص داده شده</h5>
                                        <div class="card-tools">
                                            <button class="btn btn-sm btn-secondary" onclick="clearVoiceText()">
                                                <i class="fas fa-trash"></i> پاک کردن
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <textarea 
                                            id="voiceText" 
                                            class="form-control" 
                                            rows="6" 
                                            readonly
                                            style="direction: rtl; background: #f8f9fa; font-size: 15px; line-height: 2;"
                                            placeholder="متن تشخیص داده شده از صدا..."
                                        ></textarea>
                                        <div class="mt-2">
                                            <span class="badge badge-info" id="voiceWordCount">تعداد کلمات: ۰</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- دکمه تحلیل -->
                    <div class="row mt-4">
                        <div class="col-12 text-center">
                            <button 
                                class="btn btn-primary btn-lg" 
                                id="analyzeBtn" 
                                onclick="analyzeRequirement()"
                                style="border-radius: 50px; padding: 15px 50px; font-size: 18px;"
                            >
                                <i class="fas fa-robot"></i> استخراج و تحلیل نیازمندی
                            </button>
                            <button 
                                class="btn btn-secondary btn-lg" 
                                onclick="resetAll()"
                                style="border-radius: 50px; padding: 15px 30px; font-size: 18px;"
                            >
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>

                    <!-- بارگذاری -->
                    <div id="loadingSection" style="display: none; text-align: center; padding: 40px;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">در حال تحلیل...</span>
                        </div>
                        <p class="mt-3 text-primary font-weight-bold">⏳ در حال استخراج و تحلیل...</p>
                        <div class="progress" style="height: 5px; max-width: 400px; margin: 0 auto;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%;"></div>
                        </div>
                    </div>

                    <!-- پیام‌ها -->
                    <div id="messageContainer"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- استایل‌ها -->
<!-- ========================================== -->
<style>
    .mode-section { transition: all 0.3s ease; }
    .btn-group .btn.active { box-shadow: 0 0 0 3px rgba(0,123,255,0.5); }
    
    .wave {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 50px;
        gap: 5px;
    }
    .wave span {
        display: block;
        width: 8px;
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 4px;
        animation: wave 1s ease-in-out infinite;
    }
    .wave span:nth-child(2) { animation-delay: 0.1s; }
    .wave span:nth-child(3) { animation-delay: 0.2s; }
    .wave span:nth-child(4) { animation-delay: 0.3s; }
    .wave span:nth-child(5) { animation-delay: 0.4s; }
    .wave span:nth-child(6) { animation-delay: 0.5s; }
    @keyframes wave {
        0%, 100% { height: 10px; }
        50% { height: 40px; }
    }

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
// متغیرهای عمومی
// ==========================================
let currentMode = 'text';
let recognizer = null;
let isRecording = false;
let analysisResult = null;

// ==========================================
// بخش ۱:切换 حالت ورودی
// ==========================================
function switchMode(mode) {
    currentMode = mode;
    document.getElementById('modeTextBtn').className = 'btn btn-outline-primary btn-lg' + (mode === 'text' ? ' active' : '');
    document.getElementById('modeVoiceBtn').className = 'btn btn-outline-success btn-lg' + (mode === 'voice' ? ' active' : '');
    document.getElementById('textMode').style.display = mode === 'text' ? 'block' : 'none';
    document.getElementById('voiceMode').style.display = mode === 'voice' ? 'block' : 'none';
    document.getElementById('analyzeBtn').disabled = true;
}

// ==========================================
// بخش ۲: ورود دستی متن
// ==========================================
function updateManualWordCount(text) {
    const words = text.trim().split(/\s+/).filter(w => w.length > 0);
    document.getElementById('manualWordCount').textContent = 'تعداد کلمات: ' + words.length;
    document.getElementById('analyzeBtn').disabled = text.trim().length < 10;
}

function clearManualText() {
    document.getElementById('manualText').value = '';
    updateManualWordCount('');
}

function loadSample(type) {
    const samples = {
        'payment': 'سیستم باید قابلیت پرداخت آنلاین از طریق درگاه‌های مختلف بانکی را داشته باشد. کاربران باید بتوانند با کارت‌های شتاب پرداخت کنند. امنیت پرداخت بسیار مهم است و تمام تراکنش‌ها باید رمزنگاری شوند.',
        'user': 'کاربران باید بتوانند ثبت نام کنند و پروفایل خود را مدیریت کنند. سیستم باید امکان بازیابی رمز عبور را داشته باشد. همچنین کاربران باید بتوانند اطلاعات شخصی خود را ویرایش کنند.',
        'report': 'سیستم باید گزارش‌های فروش ماهانه را تولید کند. گزارش‌ها باید با فرمت PDF و Excel قابل خروجی باشند. کاربران باید بتوانند گزارش را بر اساس تاریخ و محصول فیلتر کنند.',
        'security': 'امنیت داده‌ها بسیار مهم است. تمام اطلاعات حساس باید رمزنگاری شوند. سیستم باید از حملات سایبری محافظت شود و دسترسی‌ها بر اساس نقش کاربران کنترل شود.',
        'relationship': 'نیاز به رسیدگی به روابط بین افراد. باید بتوانیم ارتباطات بین تیم‌ها را مدیریت کنیم. همچنین نیاز به پیگیری تعاملات بین کاربران داریم.'
    };
    const textarea = document.getElementById('manualText');
    textarea.value = samples[type] || '';
    updateManualWordCount(textarea.value);
    textarea.focus();
}

// ==========================================
// بخش ۳: تشخیص گفتار (Web Speech API)
// ==========================================
class SpeechRecognizer {
    constructor() {
        this.recognition = null;
        this.isListening = false;
        this.finalTranscript = '';
        this.lastFinalText = '';
        this.lastUpdateTime = 0;
        this.updateDelay = 300;
        this.onResultCallback = null;
        this.onEndCallback = null;
        this.onErrorCallback = null;
        this.timerInterval = null;
        this.seconds = 0;
        this.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.isSupported = !!this.SpeechRecognition;
        this.isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    start(onResult, onEnd, onError) {
        if (!this.isSupported) {
            if (onError) onError('مرورگر شما پشتیبانی نمی‌کند. از Chrome استفاده کنید.');
            return false;
        }
        if (this.isListening) this.stop();
        this.recognition = new this.SpeechRecognition();
        this.recognition.lang = 'fa-IR';
        this.recognition.continuous = true;
        if (this.isMobile) {
            this.recognition.interimResults = false;
            this.recognition.maxAlternatives = 1;
            this.updateDelay = 500;
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
            try { this.recognition.stop(); } catch(e) {}
        }
        this._cleanup();
    }

    clear() {
        this.finalTranscript = '';
        this.lastFinalText = '';
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
        if (this.timerInterval) clearInterval(this.timerInterval);
        this.timerInterval = setInterval(() => {
            this.seconds++;
            const mins = String(Math.floor(this.seconds / 60)).padStart(2, '0');
            const secs = String(this.seconds % 60).padStart(2, '0');
            this._updateTimer(`${mins}:${secs}`);
        }, 1000);
    }

    _handleResult(event) {
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
            if (trimmedFinal !== this.lastFinalText) {
                this.finalTranscript += finalText;
                this.lastFinalText = trimmedFinal;
            }
            this.interimTranscript = '';
        } else if (this.isMobile) {
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
        switch(event.error) {
            case 'not-allowed': msg = '❌ دسترسی به میکروفون مجاز نیست.';
                break;
            case 'no-speech': msg = '⚠️ صدایی تشخیص داده نشد.';
                break;
            case 'audio-capture': msg = '❌ میکروفون پیدا نشد.';
                break;
            default: msg = `❌ خطا: ${event.error}`;
        }
        if (this.onErrorCallback) this.onErrorCallback(msg, event.error);
        if (['not-allowed','audio-capture'].includes(event.error)) this.stop();
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
        this._updateTimer('۰۰:۰۰');
    }

    _updateTimer(text) {
        const el = document.getElementById('recordingTimer');
        if (el) { el.textContent = text; el.style.display = 'block'; }
    }
}

// ==========================================
// بخش ۴: کنترل ضبط صدا
// ==========================================
function startRecording() {
    if (recognizer) { recognizer.stop(); recognizer = null; }
    recognizer = new SpeechRecognizer();
    const started = recognizer.start(
        (result) => {
            const textarea = document.getElementById('voiceText');
            if (textarea) {
                textarea.value = result.full;
                updateVoiceWordCount(result.full);
            }
            const analyzeBtn = document.getElementById('analyzeBtn');
            if (analyzeBtn && result.full.trim().length > 10) {
                analyzeBtn.disabled = false;
            }
        },
        (data) => {
            isRecording = false;
            document.getElementById('startRecordBtn').disabled = false;
            document.getElementById('stopRecordBtn').disabled = true;
            document.getElementById('recordingWave').style.display = 'none';
            if (data.hasText) {
                document.getElementById('statusText').textContent = '✅ ضبط کامل شد';
                document.getElementById('statusText').className = 'badge badge-success';
                showMessage('✅ تشخیص گفتار با موفقیت انجام شد.', 'success');
            }
        },
        (message) => {
            isRecording = false;
            document.getElementById('startRecordBtn').disabled = false;
            document.getElementById('stopRecordBtn').disabled = true;
            document.getElementById('recordingWave').style.display = 'none';
            showMessage(message, 'error');
        }
    );
    if (started) {
        isRecording = true;
        document.getElementById('startRecordBtn').disabled = true;
        document.getElementById('stopRecordBtn').disabled = false;
        document.getElementById('recordingWave').style.display = 'block';
        document.getElementById('statusText').textContent = '🎤 در حال گوش دادن...';
        document.getElementById('statusText').className = 'badge badge-info';
        document.getElementById('recordingTimer').style.display = 'block';
        document.getElementById('voiceText').value = '';
        updateVoiceWordCount('');
        document.getElementById('analyzeBtn').disabled = true;
        showMessage('🎤 ضبط صدا شروع شد...', 'info');
    }
}

function stopRecording() {
    if (recognizer && recognizer.isActive()) {
        recognizer.stop();
        isRecording = false;
        document.getElementById('startRecordBtn').disabled = false;
        document.getElementById('stopRecordBtn').disabled = true;
        document.getElementById('recordingWave').style.display = 'none';
        document.getElementById('statusText').textContent = '⏹️ ضبط متوقف شد';
        document.getElementById('statusText').className = 'badge badge-secondary';
    }
}

function updateVoiceWordCount(text) {
    const words = text.trim().split(/\s+/).filter(w => w.length > 0);
    document.getElementById('voiceWordCount').textContent = 'تعداد کلمات: ' + words.length;
}

function clearVoiceText() {
    document.getElementById('voiceText').value = '';
    updateVoiceWordCount('');
    document.getElementById('analyzeBtn').disabled = true;
    if (recognizer) recognizer.clear();
}

// ==========================================
// بخش ۵: تحلیل یکپارچه
// ==========================================
async function analyzeRequirement() {
    let text = '';
    if (currentMode === 'text') {
        text = document.getElementById('manualText').value.trim();
    } else {
        text = document.getElementById('voiceText').value.trim();
    }
    
    if (!text) {
        showMessage('⚠️ لطفاً ابتدا متن را وارد کنید یا صدای خود را ضبط کنید.', 'error');
        return;
    }
    if (text.length < 10) {
        showMessage('⚠️ متن باید حداقل ۱۰ کاراکتر باشد.', 'error');
        return;
    }

    const loadingSection = document.getElementById('loadingSection');
    const analyzeBtn = document.getElementById('analyzeBtn');
    
    loadingSection.style.display = 'block';
    analyzeBtn.disabled = true;
    analyzeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال تحلیل...';
    clearMessages();

    try {
        const response = await fetch('/babok/public/?route=requirement_analyze', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ text: text })
        });

        if (!response.ok) throw new Error('خطا در ارتباط با سرور: ' + response.status);
        
        const data = await response.json();

        if (data.success) {
            analysisResult = data.data;
            // ذخیره در localStorage برای صفحه نتایج
            localStorage.setItem('analysisResult', JSON.stringify({
                data: data.data,
                text: text
            }));
            // رفتن به صفحه نتایج
            window.location.href = '/babok/public/?route=requirement_result';
        } else {
            throw new Error(data.error || 'خطا در تحلیل');
        }

    } catch (error) {
        showMessage('❌ خطا: ' + error.message, 'error');
        console.error('Error:', error);
    } finally {
        loadingSection.style.display = 'none';
        analyzeBtn.disabled = false;
        analyzeBtn.innerHTML = '<i class="fas fa-robot"></i> استخراج و تحلیل نیازمندی';
    }
}

// ==========================================
// بخش ۶: توابع کمکی
// ==========================================
function showMessage(message, type = 'info') {
    const container = document.getElementById('messageContainer');
    const msgDiv = document.createElement('div');
    msgDiv.className = `message ${type}`;
    msgDiv.innerHTML = message;
    container.appendChild(msgDiv);
    setTimeout(() => { if (msgDiv.parentNode) msgDiv.remove(); }, 8000);
}

function clearMessages() {
    document.getElementById('messageContainer').innerHTML = '';
}

function resetAll() {
    clearManualText();
    clearVoiceText();
    document.getElementById('messageContainer').innerHTML = '';
    document.getElementById('recordingTimer').style.display = 'none';
    document.getElementById('recordingWave').style.display = 'none';
    document.getElementById('statusText').textContent = 'آماده برای ضبط';
    document.getElementById('statusText').className = 'badge badge-secondary';
    document.getElementById('startRecordBtn').disabled = false;
    document.getElementById('stopRecordBtn').disabled = true;
    document.getElementById('analyzeBtn').disabled = true;
    document.getElementById('analyzeBtn').innerHTML = '<i class="fas fa-robot"></i> استخراج و تحلیل نیازمندی';
    if (recognizer) { recognizer.stop(); recognizer = null; }
    isRecording = false;
    analysisResult = null;
    localStorage.removeItem('analysisResult');
    showMessage('🔄 همه چیز پاک شد.', 'info');
}

// ==========================================
// راه‌اندازی اولیه
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ صفحه استخراج و تحلیل نیازمندی بارگذاری شد');
    document.getElementById('analyzeBtn').disabled = true;
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/main.php';
?>