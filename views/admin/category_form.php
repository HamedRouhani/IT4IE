<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $category ? '✏️ ویرایش دسته‌بندی' : '➕ ایجاد دسته‌بندی جدید'; ?></h1>
            <a href="/admin/categories" style="color: var(--primary); text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
        
        <div class="admin-form">
            <form method="POST" action="<?php echo $category ? '/admin/categories/edit/' . $category['id'] : '/admin/categories/create'; ?>">
                
                <div class="form-group">
                    <label for="name">نام دسته‌بندی *</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" 
                           placeholder="مثلاً: تحلیل کسب‌وکار" required>
                </div>
                
                <div class="form-group">
                    <label for="slug">اسلاگ (URL)</label>
                    <input type="text" id="slug" name="slug" class="form-control" 
                           value="<?php echo htmlspecialchars($category['slug'] ?? ''); ?>" 
                           placeholder="خالی بگذارید تا خودکار ساخته شود">
                    <small style="color: var(--gray);">مثال: `business-analysis` → `/category/business-analysis`</small>
                </div>
                
                <div class="form-group">
                    <label for="icon">آیکون (Font Awesome)</label>
                    <input type="text" id="icon" name="icon" class="form-control" 
                           value="<?php echo htmlspecialchars($category['icon'] ?? 'fas fa-folder'); ?>" 
                           placeholder="fas fa-briefcase">
                    <small style="color: var(--gray);">از <a href="https://fontawesome.com/icons" target="_blank">fontawesome.com</a> انتخاب کنید. مثال: `fas fa-chart-line`</small>
                    <div style="margin-top: 8px;">
                        <i class="<?php echo htmlspecialchars($category['icon'] ?? 'fas fa-folder'); ?>" id="iconPreview" style="font-size: 2rem; color: var(--primary);"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="parent_id">دسته والد (اختیاری)</label>
                    <select id="parent_id" name="parent_id" class="form-control">
                        <option value="">— بدون والد (دسته اصلی) —</option>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!$category || $cat['id'] != $category['id']): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($category['parent_id']) && $category['parent_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--gray);">برای ایجاد زیردسته، یک دسته والد انتخاب کنید.</small>
                </div>
                
                <div class="form-group">
                    <label for="description">توضیحات</label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="توضیح کوتاه درباره این دسته‌بندی..."><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                </div>
                
                <div style="margin-top: 25px; display: flex; gap: 10px;">
                    <button type="submit" class="btn-admin-submit">
                        <i class="fas fa-save"></i> 
                        <?php echo $category ? 'ذخیره تغییرات' : 'ایجاد دسته‌بندی'; ?>
                    </button>
                    <a href="/admin/categories" style="padding: 10px 28px; background: var(--gray-light); color: var(--dark); border-radius: var(--radius-full); text-decoration: none;">
                        انصراف
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// پیش‌نمایش آیکون
document.getElementById('icon').addEventListener('input', function() {
    document.getElementById('iconPreview').className = this.value;
});
</script>