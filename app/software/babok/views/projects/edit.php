<?php
/**
 * ویو ویرایش پروژه
 * مسیر: app/software/babok/views/projects/edit.php
 */
$pageTitle = 'ویرایش پروژه: ' . $project['name'] . ' - BABOK Analyzer';
$activePage = 'projects';
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-edit"></i> ویرایش پروژه: <?= htmlspecialchars($project['name']) ?>
        </div>
        <div class="card-tools">
            <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-primary">
                <i class="fas fa-eye"></i> مشاهده
            </a>
            <a href="?route=projects" class="btn btn-secondary">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
    </div>
    
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="alert-software warning">
            <i class="fas fa-info-circle"></i>
            <span>
                شما در حالت مهمان هستید. برای ویرایش پروژه، ابتدا 
                <a href="/login" style="color: var(--soft-secondary);">وارد شوید</a>.
            </span>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="?route=projects_update&id=<?= $project['id'] ?>">
        <div class="form-group">
            <label class="form-label" for="name">نام پروژه *</label>
            <input type="text" id="name" name="name" class="form-control" required 
                   value="<?= htmlspecialchars($project['name']) ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label" for="description">توضیحات پروژه</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="phase">فاز فعلی پروژه</label>
                <select id="phase" name="phase" class="form-control">
                    <option value="initiation" <?= $project['phase'] === 'initiation' ? 'selected' : '' ?>>شروع (Initiation)</option>
                    <option value="planning" <?= $project['phase'] === 'planning' ? 'selected' : '' ?>>برنامه‌ریزی (Planning)</option>
                    <option value="analysis" <?= $project['phase'] === 'analysis' ? 'selected' : '' ?>>تحلیل (Analysis)</option>
                    <option value="design" <?= $project['phase'] === 'design' ? 'selected' : '' ?>>طراحی (Design)</option>
                    <option value="implementation" <?= $project['phase'] === 'implementation' ? 'selected' : '' ?>>پیاده‌سازی (Implementation)</option>
                    <option value="evaluation" <?= $project['phase'] === 'evaluation' ? 'selected' : '' ?>>ارزیابی (Evaluation)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="methodology">متدولوژی توسعه</label>
                <select id="methodology" name="methodology" class="form-control">
                    <option value="waterfall" <?= $project['methodology'] === 'waterfall' ? 'selected' : '' ?>>آبشاری (Waterfall)</option>
                    <option value="agile" <?= $project['methodology'] === 'agile' ? 'selected' : '' ?>>چابک (Agile)</option>
                    <option value="hybrid" <?= $project['methodology'] === 'hybrid' ? 'selected' : '' ?>>ترکیبی (Hybrid)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="stakeholder_count">تعداد ذی‌نفعان</label>
                <input type="number" id="stakeholder_count" name="stakeholder_count" 
                       class="form-control" min="0" value="<?= $project['stakeholder_count'] ?>">
            </div>
        </div>
        
        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="?route=projects_view&id=<?= $project['id'] ?>" class="btn btn-secondary btn-lg">انصراف</a>
        </div>
    </form>
</div>