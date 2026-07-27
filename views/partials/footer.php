<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <h4>IT4IE</h4>
                <p>لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای</p>
            </div>
            <div>
                <h4>لینک‌های سریع</h4>
                <ul>
                    <li><a href="/">خانه</a></li>
                    <li><a href="/about">درباره ما</a></li>
                    <li><a href="/contact">تماس با ما</a></li>
                    <li><a href="/software">نرم‌افزارها</a></li>
                </ul>
            </div>
            <div>
                <h4>تماس با ما</h4>
                <ul>
                    <li><i class="fas fa-envelope"></i> <?php echo $settings['admin_email'] ?? 'info@it4ie.ir'; ?></li>
                    <li><i class="fas fa-phone"></i> <?php echo $settings['contact_phone'] ?? '۰۲۱-۱۲۳۴-۵۶۷۸'; ?></li>
                    <li><i class="fas fa-map-marker-alt"></i> <?php echo $settings['contact_address'] ?? 'تهران، خیابان ولیعصر، پلاک ۱۲۳'; ?></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 IT4IE. تمامی حقوق محفوظ است.</p>
        </div>
    </div>
</footer>