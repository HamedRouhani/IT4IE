<div class="admin-container">
    <div class="admin-sidebar">
        <div class="admin-brand">
            <h3>📊 مدیریت</h3>
            <span>پنل مدیریت IT4IE</span>
        </div>
        <ul>
            <li><a href="/admin"><i class="fas fa-tachometer-alt"></i> داشبورد</a></li>
            <li><a href="/admin/posts" class="active"><i class="fas fa-file-alt"></i> پست‌ها</a></li>
            <li><a href="/admin/messages"><i class="fas fa-envelope"></i> پیام‌ها</a></li>
            <li><a href="/admin/settings"><i class="fas fa-cog"></i> تنظیمات</a></li>
            <li><a href="/"><i class="fas fa-home"></i> بازگشت به سایت</a></li>
        </ul>
    </div>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $post ? '✏️ ویرایش پست' : '➕ ایجاد پست جدید'; ?></h1>
            <a href="/admin/posts" class="btn-login" style="text-decoration: none;">
                <i class="fas fa-arrow-right"></i> بازگشت
            </a>
        </div>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" style="background: white; padding: 24px; border-radius: 12px; box-shadow: var(--shadow);">
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="title" style="display: block; font-weight: 500; margin-bottom: 4px;">عنوان</label>
                <input type="text" id="title" name="title" required
                       value="<?php echo $post['title'] ?? ''; ?>"
                       style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);">
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="slug" style="display: block; font-weight: 500; margin-bottom: 4px;">Slug (آدرس)</label>
                <input type="text" id="slug" name="slug"
                       value="<?php echo $post['slug'] ?? ''; ?>"
                       style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);">
                <small style="color: var(--gray);">اگر خالی بماند، به صورت خودکار از عنوان تولید می‌شود.</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="category_id" style="display: block; font-weight: 500; margin-bottom: 4px;">دسته‌بندی</label>
                <select id="category_id" name="category_id" style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);">
                    <option value="">بدون دسته‌بندی</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['id']; ?>" <?php echo (($post['category_id'] ?? '') == $category['id']) ? 'selected' : ''; ?>>
                            <?php echo $category['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="summary" style="display: block; font-weight: 500; margin-bottom: 4px;">خلاصه</label>
                <textarea id="summary" name="summary" rows="3" style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);"><?php echo $post['summary'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="content" style="display: block; font-weight: 500; margin-bottom: 4px;">محتوا</label>
                <textarea id="content" name="content" rows="10" required style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);"><?php echo $post['content'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group" style="margin-bottom: 16px;">
                <label for="status" style="display: block; font-weight: 500; margin-bottom: 4px;">وضعیت</label>
                <select id="status" name="status" style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);">
                    <option value="draft" <?php echo (($post['status'] ?? '') == 'draft') ? 'selected' : ''; ?>>پیش‌نویس</option>
                    <option value="published" <?php echo (($post['status'] ?? '') == 'published') ? 'selected' : ''; ?>>منتشر شده</option>
                    <option value="archived" <?php echo (($post['status'] ?? '') == 'archived') ? 'selected' : ''; ?>>بایگانی</option>
                </select>
            </div>
            
            <button type="submit" class="btn-auth" style="width: auto; padding: 10px 30px;">
                <i class="fas fa-save"></i> ذخیره
            </button>
        </form>
    </div>
</div>