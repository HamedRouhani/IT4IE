<?php
$pageTitle = 'ویرایش ریسک - PMBOK';
$activePage = 'risk';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=risk">ریسک‌ها</a> /
        <span>ویرایش: <?= htmlspecialchars($risk['title']) ?></span>
    </nav>
    <h2><i class="fas fa-edit"></i> ویرایش ریسک</h2>
</div>

<div class="card">
    <form method="POST" action="?controller=risk&action=edit&id=<?= $risk['id'] ?>" class="standard-form">
        <div class="form-group">
            <label class="form-label">پروژه *</label>
            <select name="project_id" class="form-select" required>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $risk['project_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">عنوان ریسک *</label>
            <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($risk['title']) ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($risk['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">احتمال</label>
                <select name="probability" class="form-select">
                    <?php foreach (['very_low'=>'بسیار کم', 'low'=>'کم', 'medium'=>'متوسط', 'high'=>'بالا', 'very_high'=>'بسیار بالا'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($risk['probability'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">تاثیر</label>
                <select name="impact" class="form-select">
                    <?php foreach (['very_low'=>'بسیار کم', 'low'=>'کم', 'medium'=>'متوسط', 'high'=>'بالا', 'very_high'=>'بسیار بالا'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($risk['impact'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">وضعیت</label>
            <select name="status" class="form-select">
                <?php foreach (['identified'=>'شناسایی شده', 'analyzed'=>'تحلیل شده', 'planned'=>'برنامه‌ریزی شده', 'implemented'=>'اجرا شده', 'closed'=>'بسته شده'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= ($risk['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">استراتژی پاسخ</label>
            <textarea name="response_strategy" class="form-control" rows="2"><?= htmlspecialchars($risk['response_strategy'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">برنامه پاسخ</label>
            <textarea name="response_plan" class="form-control" rows="3"><?= htmlspecialchars($risk['response_plan'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">مسئول</label>
            <input type="text" name="owner" class="form-control" value="<?= htmlspecialchars($risk['owner'] ?? '') ?>">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="?controller=risk&action=show&id=<?= $risk['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>