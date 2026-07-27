<!-- فقط محتوای پست‌ها - هیرو در main.php قرار دارد -->
<section class="posts-section">
    <div class="section-header">
        <h2>آخرین مطالب</h2>
        <a href="/posts" class="view-all">مشاهده همه</a>
    </div>
    
    <div class="posts-grid">
        <?php foreach ($posts as $post): ?>
            <article class="post-card">
                <div class="post-content">
                    <h3 class="post-title">
                        <a href="/post/<?php echo $post['slug']; ?>">
                            <?php echo $post['title']; ?>
                        </a>
                    </h3>
                    <div class="post-meta">
                        <span class="post-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo jdate($post['created_at']); ?>
                        </span>
                        <?php if ($post['category_name']): ?>
                            <span class="post-category">
                                <i class="fas fa-folder"></i>
                                <a href="/category/<?php echo $post['category_slug'] ?? ''; ?>">
                                    <?php echo $post['category_name']; ?>
                                </a>
                            </span>
                        <?php endif; ?>
                        <span class="post-views">
                            <i class="fas fa-eye"></i>
                            <?php echo number_format($post['view_count'] ?? 0); ?>
                        </span>
                    </div>
                    <div class="post-summary">
                        <?php echo truncate_text($post['summary'] ?? $post['content'], 150); ?>
                    </div>
                    <a href="/post/<?php echo $post['slug']; ?>" class="read-more">
                        ادامه مطلب <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
        
        <?php if (empty($posts)): ?>
            <div class="no-posts">
                <p>هنوز مطلبی منتشر نشده است.</p>
            </div>
        <?php endif; ?>
    </div>
</section>