<?php
$score = (int) ($submission['total_score'] ?? 0);
$maxScore = (int) ($submission['max_score'] ?? 0);
$percentage = $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : null;
$riskLabels = [
    'critical' => 'بحرانی',
    'high' => 'بالا',
    'medium' => 'متوسط',
    'low' => 'پایین',
];
$riskLabel = $riskLabels[(string) ($submission['risk_level'] ?? '')] ?? 'ثبت‌نشده';
$templateContext = [
    'name' => (string) ($submission['name'] ?? 'کاربر گرامی'),
    'checklist' => (string) ($submission['checklist_title'] ?? 'چک‌لیست ارزیابی'),
    'score' => $score,
    'max_score' => $maxScore,
    'percentage' => $percentage === null ? 'ثبت‌نشده' : $percentage . '٪',
    'risk' => $riskLabel,
];
if (empty($_SESSION['_csrf_token'])) {
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
}
$csrfField = $csrfField ?? '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars($_SESSION['_csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
?>
<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>📨 ارسال پیام درون‌برنامه‌ای</h1>
            <a href="/admin/checklist/view/<?php echo $submission['id']; ?>" class="btn-register" style="padding: 8px 16px; font-size: 14px; text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت به جزئیات
            </a>
        </div>

        <div class="admin-form">
            <div style="background: rgba(108, 60, 225, 0.06); border-radius: var(--radius); padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; color: var(--gray-dark);">
                <i class="fas fa-user" style="color: var(--primary); margin-left: 6px;"></i>
                گیرنده: <strong><?php echo htmlspecialchars($submission['name']); ?></strong>
                (<?php echo htmlspecialchars($submission['email']); ?>)
                — این پیام در بخش «پیام‌های قبلی شما» در صفحه «تماس با ما» کاربر نمایش داده می‌شود.
            </div>

            <form method="POST" action="/admin/checklist/message/<?php echo (int) $submission['id']; ?>" class="checklist-message-form">
                <?= $csrfField ?>
                <div class="form-group">
                    <label for="message-template">قالب آمادهٔ پیام</label>
                    <select id="message-template" class="message-template-select">
                        <option value="">انتخاب قالب (اختیاری)</option>
                        <option value="result">ارسال نتیجهٔ ارزیابی</option>
                        <option value="consultation">دعوت به مشاوره</option>
                        <option value="followup">پیگیری ارزیابی</option>
                        <option value="more_info">درخواست اطلاعات تکمیلی</option>
                        <option value="thanks">تشکر از مشارکت</option>
                    </select>
                    <small class="message-template-hint">با انتخاب قالب، عنوان و متن پیام پر می‌شود و قبل از ارسال می‌توانید آن را ویرایش کنید.</small>
                </div>
                <div class="form-group">
                    <label for="message-subject">موضوع پیام</label>
                    <input id="message-subject" type="text" name="subject" maxlength="255" required value="<?php echo htmlspecialchars($defaultSubject, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-group">
                    <label for="message-body">متن پیام</label>
                    <textarea id="message-body" name="message" rows="9" required maxlength="10000" placeholder="قالبی انتخاب کنید یا متن پیام را بنویسید..."></textarea>
                </div>
                <button type="submit" class="btn-admin-submit">
                    <i class="fas fa-paper-plane"></i> ارسال پیام
                </button>
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    const templateContext = <?= json_encode($templateContext, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const templates = {
        result: {
            subject: 'نتیجه ارزیابی «{{checklist}}»',
            message: 'سلام {{name}} عزیز،\n\nاز اینکه ارزیابی «{{checklist}}» را تکمیل کردید سپاسگزاریم. نتیجهٔ ثبت‌شدهٔ شما {{score}} از {{max_score}} ({{percentage}}) و سطح ریسک شناسایی‌شده «{{risk}}» است.\n\nاگر دربارهٔ نتیجه یا گام‌های بهبود پرسشی دارید، از همین گفت‌وگو برای ما بنویسید.\n\nبا احترام\nتیم IT4IE'
        },
        consultation: {
            subject: 'پیشنهاد گفت‌وگوی تخصصی دربارهٔ «{{checklist}}»',
            message: 'سلام {{name}} عزیز،\n\nبا توجه به ارزیابی «{{checklist}}» و نتیجهٔ {{percentage}}، خوشحال می‌شویم در یک گفت‌وگوی تخصصی، اولویت‌های بهبود و راهکارهای متناسب با شرایط شما را بررسی کنیم.\n\nاگر مایل هستید، زمان مناسب یا پرسش خود را در پاسخ همین پیام بفرمایید.\n\nبا احترام\nتیم IT4IE'
        },
        followup: {
            subject: 'پیگیری ارزیابی «{{checklist}}»',
            message: 'سلام {{name}} عزیز،\n\nبرای پیگیری ارزیابی «{{checklist}}» پیام می‌دهیم. آیا فرصت کردید نتیجه را بررسی کنید؟ اگر برای تفسیر امتیاز {{score}} از {{max_score}} یا انتخاب گام بعدی به راهنمایی نیاز دارید، همین‌جا پاسخ دهید.\n\nبا احترام\nتیم IT4IE'
        },
        more_info: {
            subject: 'درخواست اطلاعات تکمیلی دربارهٔ ارزیابی شما',
            message: 'سلام {{name}} عزیز،\n\nبرای اینکه بتوانیم نتیجهٔ ارزیابی «{{checklist}}» را دقیق‌تر بررسی کنیم، لطفاً در صورت امکان توضیح یا اطلاعات تکمیلی مرتبط با شرایط خود را در پاسخ همین پیام ارسال کنید.\n\nسپاس\nتیم IT4IE'
        },
        thanks: {
            subject: 'سپاس از تکمیل ارزیابی «{{checklist}}»',
            message: 'سلام {{name}} عزیز،\n\nاز زمانی که برای تکمیل ارزیابی «{{checklist}}» گذاشتید سپاسگزاریم. پاسخ‌های شما به شناخت بهتر وضعیت و اولویت‌های بهبود کمک می‌کند.\n\nهر زمان پرسشی داشتید، از همین گفت‌وگو با ما در ارتباط باشید.\n\nبا احترام\nتیم IT4IE'
        }
    };

    const select = document.getElementById('message-template');
    const subject = document.getElementById('message-subject');
    const body = document.getElementById('message-body');
    if (!select || !subject || !body) return;

    const fill = (text) => text.replace(/\{\{([a-z_]+)\}\}/g, (_, key) => String(templateContext[key] ?? ''));
    select.addEventListener('change', () => {
        const template = templates[select.value];
        if (!template) return;
        subject.value = fill(template.subject);
        body.value = fill(template.message);
    });
})();
</script>
