<!-- Hero Section - داخل content-main و بالای پست‌ها -->
<div class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title"><?php echo $settings['site_name'] ?? 'IT4IE - مشاوره بین‌رشته‌ای'; ?></h1>
        <p class="hero-description"><?php echo $settings['site_description'] ?? 'لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای'; ?></p>
        <div class="hero-actions">
            <a href="/about" class="btn-secondary">درباره ما</a>
            <a href="/software" class="btn-primary">مشاهده نرم‌افزارها</a>
        </div>
    </div>
</div>

<!-- Posts Section -->
<section class="posts-section">
    <div class="section-header">
        <h2>آخرین مطالب</h2>
        <a href="/posts" class="view-all">مشاهده همه</a>
    </div>
    
    <div class="posts-grid">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <h3 class="post-title">
                    <a href="/post/<?php echo $post['slug']; ?>">
                        <?php echo $post['title']; ?>
                    </a>
                </h3>
                <div class="post-meta">
                    <span>
                        <i class="fas fa-calendar-alt"></i>
                        <?php echo jdate($post['created_at']); ?>
                    </span>
                    <?php if ($post['category_name']): ?>
                        <span>
                            <i class="fas fa-folder"></i>
                            <a href="/category/<?php echo $post['category_slug'] ?? ''; ?>">
                                <?php echo $post['category_name']; ?>
                            </a>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="post-summary">
                    <?php echo truncate_text($post['summary'] ?? $post['content'], 150); ?>
                </div>
                <a href="/post/<?php echo $post['slug']; ?>" class="read-more">
                    ادامه مطلب <i class="fas fa-arrow-left"></i>
                </a>
            </article>
        <?php endforeach; ?>
        
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <p>هنوز مطلبی منتشر نشده است.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================
     شبکه‌های اجتماعی IT4IE
     ============================================ -->
<section class="social-cta-section">
    <div class="container">
        <div class="social-cta-card">
            <div class="social-decoration">
                <div class="decoration-circle circle-1"></div>
                <div class="decoration-circle circle-2"></div>
                <div class="decoration-circle circle-3"></div>
            </div>

            <div class="social-cta-content">
                <span class="social-badge">🌐 همراه ما باشید</span>
                <h2 class="social-title">
                    IT4IE در شبکه‌های اجتماعی
                </h2>
                <p class="social-subtitle">
                    آخرین مقالات تخصصی، ابزارهای تحلیلی و نکات کاربردی در حوزه مهندسی صنایع و مدیریت پروژه را در اینستاگرام و تلگرام دنبال کنید
                </p>

                <div class="social-buttons">
                    <!-- اینستاگرام -->
                    <a href="<?= htmlspecialchars($settings['instagram_url'] ?? 'https://instagram.com/it4ie.ir') ?>"
                       target="_blank" rel="noopener noreferrer" class="social-btn instagram-btn">
                        <div class="social-icon-wrapper">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <div class="social-info">
                            <span class="social-label">اینستاگرام</span>
                            <span class="social-handle">@it4ie.ir</span>
                        </div>
                        <i class="fas fa-arrow-left social-arrow"></i>
                    </a>

                    <!-- تلگرام -->
                    <a href="<?= htmlspecialchars($settings['telegram_url'] ?? 'https://t.me/IT4IE.IR') ?>"
                       target="_blank" rel="noopener noreferrer" class="social-btn telegram-btn">
                        <div class="social-icon-wrapper">
                            <i class="fab fa-telegram-plane"></i>
                        </div>
                        <div class="social-info">
                            <span class="social-label">تلگرام</span>
                            <span class="social-handle">@IT4IE.IR</span>
                        </div>
                        <i class="fas fa-arrow-left social-arrow"></i>
                    </a>
                </div>

                <div class="social-features">
                    <div class="feature-item">
                        <i class="fas fa-book-open"></i>
                        <span>مقالات تخصصی</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-video"></i>
                        <span>ویدیوهای آموزشی</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bell"></i>
                        <span>اطلاع‌رسانی ابزارهای جدید</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>