<?php
$pageTitle = 'ثبت ریسک جدید - PMBOK';
$activePage = 'risk';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=risk">ریسک‌ها</a> /
        <span>ثبت ریسک جدید</span>
    </nav>
    <h2><i class="fas fa-plus-circle"></i> ثبت ریسک جدید</h2>
</div>

<div class="card">
    <form method="POST" action="?controller=risk&action=create" class="standard-form">
        <div class="form-group">
            <label class="form-label">پروژه *</label>
            <select name="project_id" class="form-select" required>
                <option value="">انتخاب پروژه...</option>
                <?php foreach ($projects as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">عنوان ریسک *</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">احتمال</label>
                <select name="probability" class="form-select">
                    <option value="very_low">بسیار کم (1)</option>
                    <option value="low">کم (2)</option>
                    <option value="medium" selected>متوسط (3)</option>
                    <option value="high">بالا (4)</option>
                    <option value="very_high">بسیار بالا (5)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">تاثیر</label>
                <select name="impact" class="form-select">
                    <option value="very_low">بسیار کم (1)</option>
                    <option value="low">کم (2)</option>
                    <option value="medium" selected>متوسط (3)</option>
                    <option value="high">بالا (4)</option>
                    <option value="very_high">بسیار بالا (5)</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">استراتژی پاسخ</label>
            <textarea name="response_strategy" class="form-control" rows="2" 
                      placeholder="اجتناب / کاهش / انتقال / پذیرش"></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">برنامه پاسخ</label>
            <textarea name="response_plan" class="form-control" rows="3"></textarea>
        </div>
        
        <div class="form-group">
            <label class="form-label">مسئول</label>
            <input type="text" name="owner" class="form-control">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-save"></i> ثبت ریسک
            </button>
            <a href="?controller=risk" class="btn btn-secondary">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>