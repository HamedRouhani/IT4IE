<?php
/**
 * ویو نمایش سلسله‌مراتبی بندهای استاندارد ISO 9001:2015
 * مسیر: app/software/qms/views/iso-clauses/index.php
 */
$pageTitle = 'بندهای استاندارد ISO 9001:2015';
$currentPage = 'isoclauses';
?>

<div class="container-fluid" style="padding: 20px; max-width: 1200px; margin: 0 auto;">
    
    <!-- هدر و آمار -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <h1 style="color: #2D3748; font-size: 1.8rem; margin: 0;">
            <i class="fas fa-book" style="color: #6C3CE1;"></i>
            بندهای استاندارد ISO 9001:2015
        </h1>
        <div style="display: flex; gap: 15px;">
            <span style="background: #F3F4F6; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; color: #4B5563;">
                <i class="fas fa-layer-group"></i> کل بندها: <strong><?= $stats['total'] ?></strong>
            </span>
            <span style="background: #E0E7FF; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; color: #4338CA;">
                <i class="fas fa-folder"></i> بندهای اصلی: <strong><?= $stats['main'] ?></strong>
            </span>
        </div>
    </div>

    <!-- لیست سلسله‌مراتبی (آکاردئونی) -->
    <div class="iso-clauses-tree">
        <?php if (empty($mainClauses)): ?>
            <div style="text-align: center; padding: 50px; color: #718096; background: white; border-radius: 12px;">
                <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3; margin-bottom: 15px;"></i>
                <h4>هیچ بندی ثبت نشده است</h4>
            </div>
        <?php else: ?>
            <?php foreach ($mainClauses as $main): ?>
                <div class="clause-level-1">
                    <!-- هدر بند اصلی -->
                    <div class="clause-header" onclick="toggleClause(this)">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="background: #6C3CE1; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.9rem;">
                                <?= qms_e($main['clause_number']) ?>
                            </span>
                            <span style="font-weight: 700; color: #1F2937; font-size: 1.1rem;"><?= qms_e($main['title_fa']) ?></span>
                        </div>
                        <i class="fas fa-chevron-down transition-icon"></i>
                    </div>
                    
                    <!-- بدنه بند اصلی -->
                    <div class="clause-body">
                        <?php if (!empty($main['description'])): ?>
                            <div style="padding: 15px 20px; background: #F9FAFB; border-bottom: 1px solid #E5E7EB; color: #4B5563; line-height: 1.7;">
                                <?= nl2br(qms_e($main['description'])) ?>
                            </div>
                        <?php endif; ?>

                        <!-- زیربندها (سطح ۲) -->
                        <?php if (!empty($main['children'])): ?>
                            <div style="padding: 10px 20px;">
                                <?php foreach ($main['children'] as $sub): ?>
                                    <div class="clause-level-2">
                                        <div class="clause-header sub-header" onclick="toggleClause(this)">
                                            <div style="display: flex; align-items: center; gap: 10px;">
                                                <span style="color: #6C3CE1; font-weight: 700;"><?= qms_e($sub['clause_number']) ?></span>
                                                <span style="color: #374151;"><?= qms_e($sub['title_fa']) ?></span>
                                            </div>
                                            <i class="fas fa-chevron-down transition-icon" style="font-size: 0.8rem;"></i>
                                        </div>
                                        
                                        <div class="clause-body">
                                            <?php if (!empty($sub['description'])): ?>
                                                <p style="padding: 10px 15px; color: #4B5563; font-size: 0.95rem; line-height: 1.6; margin: 0;">
                                                    <?= nl2br(qms_e($sub['description'])) ?>
                                                </p>
                                            <?php endif; ?>

                                            <!-- زیرزیربندها (سطح ۳) -->
                                            <?php if (!empty($sub['children'])): ?>
                                                <div style="padding: 0 15px 15px 15px;">
                                                    <?php foreach ($sub['children'] as $subsub): ?>
                                                        <div class="clause-level-3">
                                                            <div style="display: flex; gap: 8px; align-items: baseline;">
                                                                <i class="fas fa-angle-left" style="color: #9CA3AF; margin-top: 4px;"></i>
                                                                <strong style="color: #4B5563; font-size: 0.95rem;"><?= qms_e($subsub['clause_number']) ?> <?= qms_e($subsub['title_fa']) ?></strong>
                                                            </div>
                                                            <?php if (!empty($subsub['description'])): ?>
                                                                <p style="color: #6B7280; font-size: 0.9rem; line-height: 1.6; margin: 5px 0 15px 20px;">
                                                                    <?= nl2br(qms_e($subsub['description'])) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- استایل‌ها و اسکریپت‌های اختصاصی -->
<style>
.iso-clauses-tree {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.clause-level-1 {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    border: 1px solid #E5E7EB;
    overflow: hidden;
}

.clause-header {
    padding: 18px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    transition: background 0.2s;
    user-select: none;
}

.clause-header:hover {
    background: #F9FAFB;
}

.clause-header.sub-header {
    padding: 14px 15px;
    background: #F3F4F6;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px solid #E5E7EB;
}

.clause-header.sub-header:hover {
    background: #E5E7FF;
    border-color: #C7D2FE;
}

.clause-body {
    display: none; /* پیش‌فرض بسته است */
    border-top: 1px solid #E5E7EB;
    animation: slideDown 0.3s ease-out;
}

.clause-level-3 {
    background: #FAFAFA;
    padding: 12px 15px;
    border-radius: 8px;
    border-right: 3px solid #D1D5DB;
    margin-bottom: 8px;
}

.transition-icon {
    transition: transform 0.3s ease;
    color: #9CA3AF;
}

.clause-header.active .transition-icon {
    transform: rotate(180deg);
    color: #6C3CE1;
}

.clause-header.active {
    background: #F5F3FF;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function toggleClause(element) {
    // پیدا کردن بدنه مرتبط
    const body = element.nextElementSibling;
    
    // تغییر وضعیت کلاس active برای چرخش آیکون
    element.classList.toggle('active');
    
    // باز و بسته کردن با انیمیشن ساده
    if (body.style.display === 'block') {
        body.style.display = 'none';
    } else {
        body.style.display = 'block';
    }
}

// اختیاری: باز کردن اولین بند به صورت پیش‌فرض برای راهنمایی کاربر
document.addEventListener('DOMContentLoaded', function() {
    const firstHeader = document.querySelector('.clause-level-1 .clause-header');
    if (firstHeader) {
        // toggleClause(firstHeader); // اگر می‌خواهید اولین مورد باز باشد، این خط را از کامنت خارج کنید
    }
});
</script>