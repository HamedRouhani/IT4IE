<?php
// $categoryCounts از کنترلر ارسال شده است
$counts = $categoryCounts ?? [];
?>

<!-- Breadcrumb -->
<nav class="blog-breadcrumb">
    <a href="/"><i class="fas fa-home"></i> خانه</a>
    <span class="sep">/</span>
    <span><?php echo $currentCategory ? htmlspecialchars($currentCategory['name']) : 'همه مطالب'; ?></span>
</nav>

<!-- هدر صفحه -->
<header class="blog-header">
    <h1>
        <?php if ($currentCategory): ?>
            <i class="<?php echo htmlspecialchars($currentCategory['icon'] ?? 'fas fa-folder'); ?>"></i>
            <?php echo htmlspecialchars($currentCategory['name']); ?>
        <?php else: ?>
            <i class="fas fa-newspaper"></i>
            همه مطالب
        <?php endif; ?>
    </h1>
    <p class="blog-subtitle">
        <?php echo $totalPosts; ?> مطلب منتشر شده
        <?php if ($currentCategory && !empty($currentCategory['description'])): ?>
            — <?php echo htmlspecialchars($currentCategory['description']); ?>
        <?php endif; ?>
    </p>
</header>

<!-- Layout دو ستونه -->
<div class="blog-layout">
    
    <!-- سایدبار فیلتر -->
    <aside class="blog-sidebar">
        <div class="blog-filter-card">
            <h3><i class="fas fa-filter"></i> فیلتر بر اساس دسته</h3>
            <ul class="blog-filter-list">
                <li>
                    <a href="/posts" class="<?php echo !$currentCategory ? 'active' : ''; ?>">
                        <i class="fas fa-globe"></i>
                        <span>همه مطالب</span>
                        <span class="count"><?php echo $totalPosts; ?></span>
                    </a>
                </li>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="/posts?category=<?php echo htmlspecialchars($cat['slug']); ?>" 
                       class="<?php echo ($currentCategory && $currentCategory['id'] == $cat['id']) ? 'active' : ''; ?>">
                        <i class="<?php echo htmlspecialchars($cat['icon'] ?? 'fas fa-folder'); ?>"></i>
                        <span><?php echo htmlspecialchars($cat['name']); ?></span>
                        <span class="count"><?php echo $counts[$cat['id']] ?? 0; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

    <!-- محتوای اصلی -->
    <main class="blog-main">
        <section class="posts-section">
            
            <?php if (empty($posts)): ?>
                <div class="blog-empty">
                    <i class="fas fa-inbox"></i>
                    <h3>مطلبی یافت نشد</h3>
                    <p>در این دسته‌بندی هنوز مطلبی منتشر نشده است.</p>
                    <a href="/posts" class="btn-primary">مشاهده همه مطالب</a>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <h3 class="post-title">
                                <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h3>
                            <div class="post-meta">
                                <span>
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('Y/m/d', strtotime($post['created_at'])); ?>
                                </span>
                                <?php if (!empty($post['category_name'])): ?>
                                    <span>
                                        <i class="fas fa-folder"></i>
                                        <a href="/posts?category=<?php echo htmlspecialchars($post['category_slug'] ?? ''); ?>">
                                            <?php echo htmlspecialchars($post['category_name']); ?>
                                        </a>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="post-summary">
                                <?php 
                                $text = $post['summary'] ?? $post['content'] ?? '';
                                echo htmlspecialchars(mb_substr(strip_tags($text), 0, 150)); 
                                ?>…
                            </div>
                            <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>" class="read-more">
                                ادامه مطلب <i class="fas fa-arrow-left"></i>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- صفحه‌بندی -->
                <?php if ($totalPages > 1): ?>
                    <nav class="blog-pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory['slug']) : ''; ?>" 
                               class="page-btn">
                                <i class="fas fa-chevron-right"></i> قبلی
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory['slug']) : ''; ?>" 
                               class="page-btn <?php echo $i === $currentPage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo $currentCategory ? '&category=' . urlencode($currentCategory['slug']) : ''; ?>" 
                               class="page-btn">
                                بعدی <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
            
        </section>
    </main>
</div>