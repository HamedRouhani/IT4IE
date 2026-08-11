<?php
$pageTitle = 'ثبت عدم انطباق جدید';
$currentPage = 'nonconformities';
?>

<div class="container-fluid" style="padding: 20px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-exclamation-triangle" style="color: #EF4444;"></i>
            ثبت عدم انطباق جدید
        </h1>
        <a href="?controller=nonconformities" 
           class="btn" 
           style="background: #718096; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
            <i class="fas fa-arrow-right"></i> بازگشت
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="background: #FEE2E2; border: 1px solid #FCA5A5; color: #991B1B; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?= qms_e($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form method="POST" action="?controller=nonconformities&action=store" id="ncForm">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            
            <!-- ستون چپ: اطلاعات اصلی -->
            <div>
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                        <i class="fas fa-edit" style="color: #EF4444;"></i>
                        اطلاعات عدم انطباق
                    </h3>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                            عنوان عدم انطباق *
                        </label>
                        <input type="text" name="title" id="nc_title" required
                               style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                            شرح کامل عدم انطباق *
                            <small style="color: #6C3CE1; font-weight: normal; margin-right: 10px;">
                                (با نوشتن توضیحات، بندهای مرتبط به صورت خودکار پیشنهاد می‌شوند)
                            </small>
                        </label>
                        <textarea name="description" id="nc_description" rows="5" required
                                  style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"
                                  placeholder="توضیح دقیق و کامل عدم انطباق مشاهده شده..."></textarea>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                            وضعیت موجود مشاهده شده
                        </label>
                        <textarea name="current_situation" rows="3"
                                  style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;"
                                  placeholder="آنچه در عمل مشاهده شد..."></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                شدت *
                            </label>
                            <select name="severity" required
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="minor">🟡 جزئی</option>
                                <option value="major">🟠 عمده</option>
                                <option value="critical">🔴 بحرانی</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568;">
                                سطح ریسک
                            </label>
                            <select name="risk_level"
                                    style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                                <option value="low">کم</option>
                                <option value="medium" selected>متوسط</option>
                                <option value="high">بالا</option>
                                <option value="critical">بحرانی</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- انتخاب بند اصلی -->
                <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="margin: 0 0 20px 0; color: #2D3748;">
                        <i class="fas fa-book" style="color: #6C3CE1;"></i>
                        انتخاب بند اصلی استاندارد *
                    </h3>
                    
                    <select name="clause_id" id="main_clause" required
                            style="width: 100%; padding: 10px; border: 2px solid #E2E8F0; border-radius: 8px;">
                        <option value="">-- انتخاب کنید --</option>
                        <?php foreach ($clauses as $clause): ?>
                            <option value="<?= $clause['id'] ?>" data-number="<?= $clause['clause_number'] ?>">
                                <?= qms_e($clause['clause_number']) ?> - <?= qms_e($clause['title_fa']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <div id="selected_clause_info" style="margin-top: 10px; padding: 10px; background: #F7FAFC; border-radius: 8px; display: none;">
                    </div>
                </div>
            </div>

            <!-- ستون راست: پیشنهادات هوشمند + اطلاعات تکمیلی -->
            <div>
                <!-- پیشنهادات هوشمند -->
                <div style="background: linear-gradient(135deg, #6C3CE1, #8B6FE8); color: white; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="margin: 0 0 15px 0;">
                        <i class="fas fa-lightbulb"></i>
                        پیشنهادات هوشمند
                    </h3>
                    <p style="font-size: 0.9rem; opacity: 0.9; margin-bottom: 15px;">
                        بندهای مرتبط با این عدم انطباق (با درصد تطابق)
                    </p>
                    
                    <button type="button" id="suggestBtn" 
                            style="width: 100%; background: white; color: #6C3CE1; padding: 10px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; margin-bottom: 15px;">
                        <i class="fas fa-magic"></i> تحلیل و پیشنهاد بندها
                    </button>

                    <div id="suggestions_list" style="max-height: 400px; overflow-y: auto;">
                        <p style="text-align: center; opacity: 0.8; font-size: 0.9rem;">
                            ابتدا عنوان و توضیحات را وارد کنید
                        </p>
                    </div>
                </div>

                <!-- اطلاعات تکمیلی -->
                <div style="background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h4 style="margin: 0 0 15px 0; color: #2D3748;">
                        <i class="fas fa-info-circle" style="color: #6C3CE1;"></i>
                        اطلاعات تکمیلی
                    </h4>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                            واحد متأثر
                        </label>
                        <select name="affected_department_id"
                                style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                            <option value="">-- انتخاب کنید --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"><?= qms_e($dept['name_fa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                            فرآیند متأثر
                        </label>
                        <input type="text" name="affected_process"
                               style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; color: #4A5568; font-size: 0.9rem;">
                            منبع کشف
                        </label>
                        <select name="detection_source"
                                style="width: 100%; padding: 8px; border: 2px solid #E2E8F0; border-radius: 8px;">
                            <option value="internal_audit">ممیزی داخلی</option>
                            <option value="external_audit">ممیزی خارجی</option>
                            <option value="customer_complaint">شکایت مشتری</option>
                            <option value="process_monitoring">پایش فرآیند</option>
                            <option value="management_review">بازنگری مدیریت</option>
                            <option value="other">سایر</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- فیلدهای مخفی برای بندهای مرتبط انتخاب شده -->
        <div id="related_clauses_container"></div>

        <!-- دکمه ثبت -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid #E2E8F0; display: flex; gap: 10px;">
            <button type="submit" 
                    style="background: linear-gradient(135deg, #EF4444, #F87171); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-save"></i> ثبت عدم انطباق
            </button>
            <a href="?controller=nonconformities" 
               style="background: #718096; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    let selectedRelatedClauses = [];

    // نمایش اطلاعات بند انتخاب شده
    $('#main_clause').on('change', function() {
        const $selected = $(this).find('option:selected');
        const clauseNumber = $selected.data('number');
        const clauseText = $selected.text();
        
        if (clauseNumber) {
            $('#selected_clause_info').html(
                '<strong>بند ' + clauseNumber + ':</strong> ' + clauseText
            ).show();
        } else {
            $('#selected_clause_info').hide();
        }
    });

    // دکمه پیشنهاد هوشمند
    $('#suggestBtn').on('click', function() {
        const title = $('#nc_title').val();
        const description = $('#nc_description').val();
        
        if (!title && !description) {
            alert('لطفاً ابتدا عنوان یا توضیحات را وارد کنید.');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> در حال تحلیل...');
        
        $('#suggestions_list').html('<p style="text-align: center; opacity: 0.8;">در حال تحلیل...</p>');

        $.ajax({
            url: '?controller=nonconformities&action=suggestClauses',
            method: 'POST',
            data: { title: title, description: description },
            dataType: 'json',
            success: function(response) {
                $btn.prop('disabled', false).html('<i class="fas fa-magic"></i> تحلیل و پیشنهاد بندها');
                
                if (response.success && response.suggestions.length > 0) {
                    let html = '';
                    const mainClauseId = $('#main_clause').val();
                    
                    response.suggestions.forEach(function(s) {
                        const isSelected = s.id == mainClauseId;
                        const isAlreadyAdded = selectedRelatedClauses.some(c => c.clause_id == s.id);
                        
                        html += `
                            <div style="background: rgba(255,255,255,0.15); padding: 10px; border-radius: 8px; margin-bottom: 8px; ${isSelected ? 'opacity: 0.5;' : ''}">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 5px;">
                                    <strong style="font-size: 0.9rem;">${s.clause_number}</strong>
                                    <span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 10px; font-size: 0.75rem;">
                                        ${s.percentage}% تطابق
                                    </span>
                                </div>
                                <div style="font-size: 0.85rem; margin-bottom: 8px;">${s.title_fa}</div>
                                ${isSelected ? '<small style="opacity: 0.8;">✓ به عنوان بند اصلی انتخاب شده</small>' : 
                                    (isAlreadyAdded ? '<small style="opacity: 0.8;">✓ قبلاً اضافه شده</small>' :
                                    `<button type="button" class="add-related-btn" 
                                             data-id="${s.id}" 
                                             data-number="${s.clause_number}" 
                                             data-title="${s.title_fa}" 
                                             data-percentage="${s.percentage}"
                                             style="width: 100%; background: white; color: #6C3CE1; padding: 5px; border: none; border-radius: 5px; cursor: pointer; font-size: 0.8rem;">
                                        <i class="fas fa-plus"></i> افزودن به بندهای مرتبط
                                    </button>`)}
                            </div>
                        `;
                    });
                    
                    $('#suggestions_list').html(html);
                    
                    // اتصال رویداد به دکمه‌های افزودن
                    $('.add-related-btn').on('click', function() {
                        const clauseId = $(this).data('id');
                        const clauseNumber = $(this).data('number');
                        const clauseTitle = $(this).data('title');
                        const percentage = $(this).data('percentage');
                        
                        if (!selectedRelatedClauses.some(c => c.clause_id == clauseId)) {
                            selectedRelatedClauses.push({
                                clause_id: clauseId,
                                percentage: percentage
                            });
                            renderRelatedClauses();
                            $(this).prop('disabled', true).text('✓ اضافه شد').css('opacity', '0.5');
                        }
                    });
                } else {
                    $('#suggestions_list').html('<p style="text-align: center; opacity: 0.8; font-size: 0.9rem;">پیشنهادی یافت نشد</p>');
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-magic"></i> تحلیل و پیشنهاد بندها');
                $('#suggestions_list').html('<p style="text-align: center; opacity: 0.8;">خطا در تحلیل</p>');
            }
        });
    });

    // رندر لیست بندهای مرتبط انتخاب شده
    function renderRelatedClauses() {
        let html = '';
        selectedRelatedClauses.forEach(function(c, index) {
            html += `
                <input type="hidden" name="related_clauses[${index}][clause_id]" value="${c.clause_id}">
                <input type="hidden" name="related_clauses[${index}][percentage]" value="${c.percentage}">
            `;
        });
        $('#related_clauses_container').html(html);
    }
});
</script>