<div class="admin-container">
    
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    
    <div class="admin-content">
        <div class="admin-header">
            <h1>⚙️ تنظیمات سایت</h1>
        </div>
        
        <form method="POST" action="/admin/settings" style="background: white; padding: 24px; border-radius: 12px; box-shadow: var(--shadow);">
            <?php foreach ($settings as $key => $value): ?>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label for="<?php echo $key; ?>" style="display: block; font-weight: 500; margin-bottom: 4px;">
                        <?php echo str_replace('_', ' ', ucfirst($key)); ?>
                    </label>
                    <?php if (strpos($value, "\n") !== false || strlen($value) > 200): ?>
                        <textarea id="<?php echo $key; ?>" name="<?php echo $key; ?>" rows="3" 
                                  style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);"><?php echo htmlspecialchars($value); ?></textarea>
                    <?php else: ?>
                        <input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" 
                               value="<?php echo htmlspecialchars($value); ?>"
                               style="width: 100%; padding: 10px 12px; border: 2px solid var(--gray-light); border-radius: 8px; font-family: var(--font-family);">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" name="submit" class="btn-auth" style="width: auto; padding: 10px 30px;">
                <i class="fas fa-save"></i> ذخیره تنظیمات
            </button>
        </form>
    </div>
</div>