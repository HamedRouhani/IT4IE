<?php
$pageTitle = 'ایجاد پروژه جدید - PMBOK';
$activePage = 'project';
?>

<div class="page-header">
    <nav class="breadcrumb">
        <a href="?controller=project">پروژه‌ها</a> /
        <span>ایجاد پروژه جدید</span>
    </nav>
    <h2><i class="fas fa-plus-circle"></i> ایجاد پروژه جدید</h2>
</div>

<div class="card">
    <form method="POST" action="?controller=project&action=create" class="standard-form">
        <div class="form-group">
            <label class="form-label">نام پروژه *</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">توضیحات</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">فاز پروژه</label>
                <select name="phase" class="form-select">
                    <option value="initiation" selected>آغاز</option>
                    <option value="planning">برنامه‌ریزی</option>
                    <option value="execution">اجرا</option>
                    <option value="monitoring_controlling">نظارت و کنترل</option>
                    <option value="closure">اختتام</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">متدولوژی</label>
                <select name="methodology" class="form-select">
                    <option value="hybrid" selected>ترکیبی</option>
                    <option value="waterfall">آبشاری</option>
                    <option value="agile">چابک</option>
                    <option value="adaptive">تطبیقی</option>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> ذخیره پروژه
            </button>
            <a href="?controller=project" class="btn btn-secondary">
                <i class="fas fa-times"></i> انصراف
            </a>
        </div>
    </form>
</div>