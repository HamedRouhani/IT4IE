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