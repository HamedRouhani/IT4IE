<?php
$pageTitle = 'ایجاد بازنگری مدیریت جدید';
$currentPage = 'managementreviews';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-users-cog" style="color: #6C3CE1;"></i>
            ثبت صورت‌جلسه بازنگری مدیریت
        </h1>
        <a href="?controller=managementreviews" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <form method="POST" action="?controller=managementreviews&action=store">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <!-- ستون چپ: ورودی‌ها و خروجی‌ها -->
            <div>
                <!-- اطلاعات پایه -->
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748; border-bottom: 2px solid #E2E8F0; padding-bottom: 10px;">
                        <i class="fas fa-info-circle" style="color: #6C3CE1;"></i> اطلاعات عمومی
                    </h3>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">عنوان جلسه</label>
                            <input type="text" name="title" value="بازنگری مدیریت سال <?= date('Y') ?>" required
                                   style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">تاریخ برگزاری *</label>
                            <input type="text" name="review_date_display" class="persian-date-input" data-target="review_date" placeholder="1405/05/20" required
                                   style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                            <input type="hidden" name="review_date" id="review_date" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">محل برگزاری</label>
                            <input type="text" name="meeting_location" placeholder="مثال: سالن کنفرانس اصلی"
                                   style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">وضعیت</label>
                            <select name="status" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="draft">پیش‌نویس</option>
                                <option value="scheduled">زمان‌بندی شده</option>
                                <option value="completed">تکمیل شده</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ورودی‌های بازنگری (بند 9.3.2) -->
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-right: 4px solid #3B82F6;">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                        <i class="fas fa-arrow-down" style="color: #3B82F6;"></i> ورودی‌های بازنگری (بند 9.3.2)
                    </h3>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۱. وضعیت اقدامات بازنگری‌های قبلی</label>
                        <textarea name="previous_actions_status" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۲. تغییرات در مسائل خارجی و داخلی مرتبط با QMS</label>
                        <textarea name="changes_in_context" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۳. اطلاعات عملکرد و اثربخشی QMS (رضایت مشتری، عدم انطباق‌ها، نتایج ممیزی و...)</label>
                        <textarea name="performance_effectiveness" rows="4" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۴. کفایت منابع</label>
                        <textarea name="resource_adequacy" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۵. اثربخشی اقدامات انجام‌شده برای رسیدگی به ریسک‌ها و فرصت‌ها</label>
                        <textarea name="risk_effectiveness" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۶. فرصت‌های بهبود</label>
                        <textarea name="improvement_opportunities_input" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                </div>

                <!-- خروجی‌های بازنگری (بند 9.3.3) -->
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-right: 4px solid #10B981;">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                        <i class="fas fa-arrow-up" style="color: #10B981;"></i> خروجی‌های بازنگری (بند 9.3.3)
                    </h3>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۱. تصمیمات و اقدامات مربوط به فرصت‌های بهبود</label>
                        <textarea name="improvement_actions" rows="3" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۲. هرگونه نیاز به تغییرات در سیستم مدیریت کیفیت</label>
                        <textarea name="qms_changes" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۳. نیازمندی‌های منابع</label>
                        <textarea name="resource_needs" rows="2" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">۴. سایر تصمیمات کلیدی اتخاذ شده</label>
                        <textarea name="decisions_made" rows="3" style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"></textarea>
                    </div>
                </div>
            </div>

            <!-- ستون راست: راهنما و ثبت -->
            <div>
                <div style="background: #FEF3C7; border-radius: 12px; padding: 20px; margin-bottom: 20px; border-right: 4px solid #F59E0B;">
                    <h4 style="margin: 0 0 10px 0; color: #92400E;"><i class="fas fa-lightbulb"></i> نکته استاندارد</h4>
                    <p style="margin: 0; color: #78350F; font-size: 0.9rem; line-height: 1.6;">
                        طبق بند 9.3.1، بازنگری مدیریت باید در فواصل برنامه‌ریزی‌شده انجام شود تا از تناسب، کفایت و اثربخشی مداوم QMS اطمینان حاصل شود.
                    </p>
                </div>

                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: sticky; top: 20px;">
                    <button type="submit" 
                            style="width: 100%; background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; padding: 12px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 1rem;">
                        <i class="fas fa-save"></i> ذخیره صورت‌جلسه
                    </button>
                    <a href="?controller=managementreviews" 
                       style="display: block; text-align: center; margin-top: 10px; color: #718096; text-decoration: none;">
                        انصراف
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- اسکریپت تاریخ شمسی (اگر در layout اصلی لود نشده) -->
<link rel="stylesheet" href="https://unpkg.com/persian-datepicker@1.2.0/dist/css/persian-datepicker.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/persian-date@1.1.0/dist/persian-date.min.js"></script>
<script src="https://unpkg.com/persian-datepicker@1.2.0/dist/js/persian-datepicker.min.js"></script>
<script>
$(document).ready(function() {
    $('.persian-date-input').each(function() {
        var $input = $(this);
        var targetId = $input.data('target');
        var $hiddenInput = $('#' + targetId);

        $input.persianDatepicker({
            format: 'YYYY/MM/DD',
            autoClose: true,
            initialValue: false,
            calendar: { persian: { locale: 'fa' } },
            onSelect: function(unix) {
                var pd = new persianDate(unix);
                $hiddenInput.val(pd.toCalendar('gregorian').format('YYYY-MM-DD'));
            }
        });
    });
});
</script>