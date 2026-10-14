<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4>IT4IE</h4>
                <p><?php echo $settings['site_description'] ?? 'لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای'; ?></p>
            </div>
            <div>
                <h4>لینک‌های سریع</h4>
                <ul>
                    <li><a href="/">خانه</a></li>
                    <li><a href="/about">درباره ما</a></li>
                    <li><a href="/contact">تماس با ما</a></li>
                    <li><a href="/software">نرم‌افزارها</a></li>
                    <li><a href="/pricing">تعرفه‌ها</a></li>
                    <li><a href="/billing/my">اشتراک من</a></li>
                </ul>
            </div>
            <div>
                <h4>تماس با ما</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($settings['admin_email'] ?? 'info@it4ie.ir', ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php /* تلفن و نشانی فقط وقتی در تنظیمات سایت ثبت شده باشند نمایش داده می‌شوند (بدون مقدار پیش‌فرض ساختگی) */ ?>
                    <?php if (!empty($settings['contact_phone'])): ?>
                    <li><i class="fas fa-phone"></i> <?php echo htmlspecialchars($settings['contact_phone'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                    <?php if (!empty($settings['contact_address'])): ?>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($settings['contact_address'], ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> IT4IE. تمامی حقوق محفوظ است.</p>
        </div>
    </div>
</footer>