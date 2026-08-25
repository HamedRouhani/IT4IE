<?php
/**
 * دستیار هوشمند MCDM
 */
?>

<div class="page-header">
    <div>
        <h2><i class="fas fa-robot"></i> دستیار هوشمند MCDM</h2>
        <div class="breadcrumb">
            <a href="<?= mcdm_url('controller=dashboard') ?>">داشبورد</a> / دستیار هوشمند
        </div>
    </div>
</div>

<div class="ai-panel">
    <div class="ai-head">
        <i class="fas fa-brain"></i>
        <div>
            <strong>پیشنهاد روش مناسب</strong>
            <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">بر اساس ویژگی‌های مسئله شما</p>
        </div>
    </div>
    
    <form method="POST" style="padding: 20px;">
        <h4 style="margin-bottom: 15px; color: var(--mcdm-olive-900);">ویژگی‌های مسئله تصمیم‌گیری</h4>
        
        <div class="form-row" style="margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label">تعداد گزینه‌ها</label>
                <input type="number" name="alt_count" class="form-control" value="4" min="2" max="20" required>
            </div>
            <div class="form-group">
                <label class="form-label">صنعت</label>
                <select name="industry" class="form-select">
                    <option value="MFG">تولیدی</option>
                    <option value="IT">فناوری اطلاعات</option>
                    <option value="OG">نفت و گاز</option>
                    <option value="SVC">خدماتی</option>
                </select>
            </div>
        </div>
        
        <h4 style="margin-bottom: 15px; color: var(--mcdm-olive-900);">ویژگی‌های مسئله (چند مورد را انتخاب کنید)</h4>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px;">
            <label class="ai-chip" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px;">
                <input type="checkbox" name="needs_weights" style="width: auto;">
                نیاز به وزن‌دهی معیارها
            </label>
            <label class="ai-chip" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px;">
                <input type="checkbox" name="expert_driven" style="width: auto;">
                قضاوت مبتنی بر خبره
            </label>
            <label class="ai-chip" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px;">
                <input type="checkbox" name="conflict" style="width: auto;">
                معیارهای متعارض
            </label>
            <label class="ai-chip" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px;">
                <input type="checkbox" name="quantitative" style="width: auto;">
                داده‌های کمی
            </label>
            <label class="ai-chip" style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px;">
                <input type="checkbox" name="transparency" style="width: auto;">
                شفافیت برای مدیریت
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary" style="width: 100%; background: var(--mcdm-olive-700); color: white;">
            <i class="fas fa-magic"></i> دریافت پیشنهاد هوشمند
        </button>
    </form>
    
    <?php if ($recommendation): ?>
    <div style="padding: 20px; border-top: 2px solid var(--mcdm-olive-200);">
        <h4 style="margin-bottom: 15px; color: var(--mcdm-olive-900);">
            <i class="fas fa-lightbulb" style="color: var(--mcdm-olive-700);"></i> توصیه روش‌ها
        </h4>
        
        <?php foreach ($recommendation as $index => $rec): ?>
        <div class="ai-insight" style="margin-bottom: 10px; padding: 12px; background: var(--mcdm-olive-50); border-radius: 8px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <strong style="color: var(--mcdm-olive-900);">#<?= $index + 1 ?> - <?= htmlspecialchars($rec['method']) ?></strong>
                <span class="badge bg-primary" style="background: var(--mcdm-olive-700); color: white; padding: 4px 10px; border-radius: 12px;">
                    امتیاز: <?= $rec['score'] ?>
                </span>
            </div>
            <p style="margin: 0; line-height: 1.6; color: var(--mcdm-olive-800);"><?= htmlspecialchars($rec['reason']) ?></p>
        </div>
        <?php endforeach; ?>
        
        <?php if (!empty($suggestedCriteria)): ?>
        <div style="margin-top: 20px;">
            <h4 style="margin-bottom: 15px; color: var(--mcdm-olive-900);">
                <i class="fas fa-list" style="color: var(--mcdm-olive-700);"></i> معیارهای پیشنهادی
            </h4>
            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                <?php foreach ($suggestedCriteria as $criterion): ?>
                <span class="ai-chip" style="padding: 6px 14px; background: var(--mcdm-olive-100); color: var(--mcdm-olive-900); border-radius: 20px; border: 1px solid var(--mcdm-olive-300);">
                    <?= htmlspecialchars($criterion) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>