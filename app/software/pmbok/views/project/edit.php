<?php
$pageTitle = 'ویرایش پروژه - PMBOK';
$activePage = 'project';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=project">پروژه‌ها</a> /
        <span>ویرایش: <?= htmlspecialchars($project['name']) ?></span>
    </nav>
    <h2><i class="fas fa-edit"></i> ویرایش پروژه</h2>
</div>

<div class="card">
    <form method="POST" action="?controller=project&action=edit&id=<?= $project['id'] ?>" class="standard-form">
        <div class="form-group">
            <label class="form-label">نام پروژه *</label>
            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($project['name']) ?>">
        </div>
        
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">فاز پروژه</label>
                <select name="phase" class="form-select">
                    <?php foreach (['initiation'=>'آغاز', 'planning'=>'برنامه‌ریزی', 'execution'=>'اجرا', 'monitoring_controlling'=>'نظارت و کنترل', 'closure'=>'اختتام'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($project['phase'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">متدولوژی</label>
                <select name="methodology" class="form-select">
                    <?php foreach (['hybrid'=>'ترکیبی', 'waterfall'=>'آبشاری', 'agile'=>'چابک', 'adaptive'=>'تطبیقی'] as $k => $v): ?>
                        <option value="<?= $k ?>" <?= ($project['methodology'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
            <a href="?controller=project&action=show&id=<?= $project['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>