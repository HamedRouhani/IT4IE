<div class="form-group mb-4">
    <label for="requirement_notes" class="form-label fw-bold">توضیحات نیازمندی / یادداشت‌ها</label>
    <textarea 
        id="requirement_notes" 
        name="notes" 
        rows="4" 
        class="form-control" 
        placeholder="نیازمندی را اینجا بنویسید (مثال: سیستم باید امکان صدور فاکتور را داشته باشد...)"></textarea>
    
    <!-- جعبه نمایش آنی امتیاز کیفیت -->
    <div id="quality-validation-box" class="mt-2 p-3 rounded border" style="display: none; transition: all 0.3s ease;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">تحلیل هوشمند کیفیت:</span>
            <span id="quality-score-badge" class="badge rounded-pill px-3 py-2">0</span>
        </div>
        
        <ul id="quality-issues" class="small text-danger mb-2 ps-3"></ul>
        <div id="quality-suggestions" class="small text-primary fw-medium"></div>
    </div>

    <!-- فیلد مخفی برای ذخیره متدولوژی پروژه (اگر در صفحه موجود است) -->
    <input type="hidden" id="project_methodology" value="hybrid"> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('requirement_notes');
    const validationBox = document.getElementById('quality-validation-box');
    const scoreBadge = document.getElementById('quality-score-badge');
    const issuesList = document.getElementById('quality-issues');
    const suggestionsDiv = document.getElementById('quality-suggestions');
    const methodologyInput = document.getElementById('project_methodology');

    let debounceTimer;

    // گوش دادن به رویداد تایپ کاربر
    textarea.addEventListener('input', function() {
        const text = this.value.trim();
        
        // پاک کردن تایمر قبلی
        clearTimeout(debounceTimer);

        // اگر متن خیلی کوتاه است، باکس را مخفی کن
        if (text.length < 15) {
            validationBox.style.display = 'none';
            return;
        }

        // تنظیم تایمر جدید (Debounce)
        debounceTimer = setTimeout(() => {
            validateRequirement(text);
        }, 600); // 600 میلی‌ثانیه تأخیر
    });

    function validateRequirement(text) {
        const methodology = methodologyInput ? methodologyInput.value : 'hybrid';
        const formData = new FormData();
        formData.append('text', text);
        formData.append('methodology', methodology);

        // نمایش حالت در حال بارگذاری
        scoreBadge.textContent = 'در حال تحلیل...';
        scoreBadge.className = 'badge rounded-pill px-3 py-2 bg-secondary';
        validationBox.style.display = 'block';

        // ارسال درخواست به کنترلر (مسیر route را مطابق سیستم خود تنظیم کنید)
        fetch('?route=requirement_validate_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // به‌روزرسانی امتیاز
            scoreBadge.textContent = data.score + '/100 (' + data.grade + ')';

            // تغییر رنگ بر اساس امتیاز
            if (data.score >= 80) {
                validationBox.style.backgroundColor = '#d1e7dd'; // سبز روشن
                validationBox.style.borderColor = '#badbcc';
                scoreBadge.className = 'badge rounded-pill px-3 py-2 bg-success';
            } else if (data.score >= 60) {
                validationBox.style.backgroundColor = '#fff3cd'; // زرد روشن
                validationBox.style.borderColor = '#ffecb5';
                scoreBadge.className = 'badge rounded-pill px-3 py-2 bg-warning text-dark';
            } else {
                validationBox.style.backgroundColor = '#f8d7da'; // قرمز روشن
                validationBox.style.borderColor = '#f5c2c7';
                scoreBadge.className = 'badge rounded-pill px-3 py-2 bg-danger';
            }

            // به‌روزرسانی لیست مشکلات
            issuesList.innerHTML = '';
            if (data.issues && data.issues.length > 0) {
                data.issues.forEach(issue => {
                    const li = document.createElement('li');
                    li.textContent = '⚠️ ' + issue;
                    issuesList.appendChild(li);
                });
            }

            // به‌روزرسانی پیشنهادات
            if (data.suggestions && data.suggestions.length > 0) {
                suggestionsDiv.innerHTML = '💡 پیشنهاد بهبود: ' + data.suggestions.join(' ');
            } else {
                suggestionsDiv.innerHTML = '✅ نیازمندی از کیفیت مطلوبی برخوردار است و آماده ثبت می‌باشد.';
            }
        })
        .catch(error => {
            console.error('خطا در اعتبارسنجی:', error);
            scoreBadge.textContent = 'خطا در تحلیل';
            scoreBadge.className = 'badge rounded-pill px-3 py-2 bg-danger';
        });
    }
});
</script>