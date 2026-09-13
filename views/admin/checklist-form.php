<div class="admin-container">
    <?php include VIEWS_PATH . '/admin/partials/sidebar.php'; ?>
    <div class="admin-content">
        <div class="admin-header">
            <h1><?= $checklist ? '✏️ ویرایش چک‌لیست: ' . htmlspecialchars($checklist['title']) : '➕ ایجاد چک‌لیست جدید' ?></h1>
            <div style="display: flex; gap: 8px;">
                <?php if ($checklist): ?>
                <a href="/admin/checklist/questions/<?= $checklist['id'] ?>" class="btn-register" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-question-circle"></i> مدیریت سوالات
                </a>
                <a href="/checklist/view/<?= htmlspecialchars($checklist['slug']) ?>" target="_blank" class="btn-register" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-eye"></i> پیش‌نمایش
                </a>
                <?php endif; ?>
                <a href="/admin/checklists" class="btn-register" style="padding: 8px 16px; font-size: 13px; text-decoration: none;">
                    <i class="fas fa-arrow-right"></i> بازگشت به لیست
                </a>
            </div>
        </div>

        <form method="POST" class="admin-form">
            <div class="form-group">
                <label>عنوان چک‌لیست *</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($checklist['title'] ?? '') ?>" placeholder="مثال: ارزیابی ریسک پروژه نرم‌افزاری">
                <small style="color: var(--gray); font-size: 0.75rem;">نامی که کاربر در کارت چک‌لیست و بالای فرم می‌بیند.</small>
            </div>

            <div class="form-group">
                <label>اسلاگ (آدرس اینترنتی)</label>
                <input type="text" name="slug" value="<?= htmlspecialchars($checklist['slug'] ?? '') ?>" placeholder="project-risk-assessment">
                <small style="color: var(--gray); font-size: 0.75rem;">فقط حروف انگلیسی، عدد و خط تیره. آدرس نهایی: <code dir="ltr">it4ie.ir/checklist/view/SLUG</code> — اگر خالی بگذارید خودکار از عنوان ساخته می‌شود.</small>
            </div>

            <div class="form-group">
                <label>توضیح کوتاه</label>
                <textarea name="description" placeholder="یک یا دو جمله درباره اینکه این چک‌لیست به چه کسی کمک می‌کند..."><?= htmlspecialchars($checklist['description'] ?? '') ?></textarea>
            </div>

            <!-- ============================================
                 انتخاب‌گر بصری آیکون
                 ============================================ -->
            <?php
            $iconOptions = [
                'fa-clipboard-list', 'fa-clipboard-check', 'fa-tasks', 'fa-check-double',
                'fa-chart-line', 'fa-chart-pie', 'fa-chart-bar', 'fa-gauge-high',
                'fa-project-diagram', 'fa-sitemap', 'fa-cogs', 'fa-users-cog',
                'fa-handshake', 'fa-briefcase', 'fa-industry', 'fa-boxes-stacked',
                'fa-database', 'fa-laptop-code', 'fa-network-wired', 'fa-shield-alt',
                'fa-triangle-exclamation', 'fa-magnifying-glass', 'fa-lightbulb', 'fa-bullseye',
                'fa-flag-checkered', 'fa-rocket', 'fa-star', 'fa-clock',
                'fa-calendar-check', 'fa-comments', 'fa-graduation-cap', 'fa-tools',
                'fa-layer-group', 'fa-puzzle-piece', 'fa-arrows-rotate', 'fa-scale-balanced',
                'fa-coins', 'fa-heart-pulse', 'fa-leaf', 'fa-file-alt'
            ];
            $currentIcon = $checklist['icon'] ?? 'fa-clipboard-list';
            ?>
            <div class="form-group">
                <label>آیکون چک‌لیست</label>
                <div class="icon-picker">
                    <!-- پیش‌نمایش زنده -->
                    <div class="icon-picker-preview">
                        <i id="iconPreviewIcon" class="fas <?= htmlspecialchars($currentIcon) ?>"></i>
                        <span id="iconPreviewName"><?= htmlspecialchars($currentIcon) ?></span>
                    </div>

                    <!-- شبکه آیکون‌ها -->
                    <div class="icon-picker-grid">
                        <?php foreach ($iconOptions as $ic): ?>
                        <button type="button"
                                class="icon-option<?= $ic === $currentIcon ? ' selected' : '' ?>"
                                data-icon="<?= $ic ?>"
                                title="<?= $ic ?>">
                            <i class="fas <?= $ic ?>"></i>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- ورودی دستی (برای آیکون دلخواه خارج از لیست) -->
                    <input type="text" id="iconInput" name="icon" value="<?= htmlspecialchars($currentIcon) ?>" placeholder="یا نام آیکون دلخواه را تایپ کنید، مثال: fa-cube">
                    <small style="color: var(--gray); font-size: 0.75rem;">روی یک آیکون کلیک کنید تا انتخاب شود، یا نام آیکون دلخواه Font Awesome را در کادر بالا تایپ کنید.</small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label>زمان تخمینی (دقیقه)</label>
                    <input type="number" name="estimated_time" min="1" value="<?= $checklist['estimated_time'] ?? 5 ?>">
                </div>
                <div class="form-group">
                    <label>ترتیب نمایش در لیست</label>
                    <input type="number" name="sort_order" value="<?= $checklist['sort_order'] ?? 0 ?>">
                    <small style="color: var(--gray); font-size: 0.75rem;">عدد کوچک‌تر = نمایش زودتر در صفحه /checklist.</small>
                </div>
            </div>

            <div class="form-group">
                <label>دسته‌بندی</label>
                <input type="text" name="category" value="<?= htmlspecialchars($checklist['category'] ?? 'general') ?>" placeholder="general">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" value="1" <?= ($checklist['is_active'] ?? 1) ? 'checked' : '' ?> style="width: auto;">
                    چک‌لیست فعال باشد (در سایت نمایش داده شود)
                </label>
                <label style="display: flex; align-items: center; gap: 8px; margin-top: 8px;">
                    <input type="checkbox" name="is_featured" value="1" <?= ($checklist['is_featured'] ?? 0) ? 'checked' : '' ?> style="width: auto;">
                    به‌عنوان چک‌لیست ویژه علامت‌گذاری شود
                </label>
            </div>

            <button type="submit" class="btn-admin-submit">
                <i class="fas fa-save"></i> ذخیره تغییرات
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    var input = document.getElementById('iconInput');
    var previewIcon = document.getElementById('iconPreviewIcon');
    var previewName = document.getElementById('iconPreviewName');
    var options = document.querySelectorAll('.icon-option');

    function sync(value) {
        previewIcon.className = 'fas ' + value;
        previewName.textContent = value;
        options.forEach(function (btn) {
            btn.classList.toggle('selected', btn.getAttribute('data-icon') === value);
        });
    }

    // کلیک روی آیکون‌های شبکه
    options.forEach(function (btn) {
        btn.addEventListener('click', function () {
            input.value = this.getAttribute('data-icon');
            sync(input.value);
        });
    });

    // تایپ دستی در کادر متنی
    input.addEventListener('input', function () {
        sync(this.value.trim());
    });
})();
</script>