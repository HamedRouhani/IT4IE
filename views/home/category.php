<?php
$category = $category ?? [];
$posts = $posts ?? [];
$categories = $categories ?? [];
?>

<div class="category-page">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="cat-breadcrumb">
            <a href="/"><i class="fas fa-home"></i> خانه</a>
            <span class="sep">/</span>
            <span><?php echo htmlspecialchars($category['name'] ?? 'دسته‌بندی'); ?></span>
        </nav>

        <!-- هدر دسته -->
        <header class="category-header">
            <h1>
                <i class="<?php echo htmlspecialchars($category['icon'] ?? 'fas fa-folder-open'); ?>"></i>
                <?php echo htmlspecialchars($category['name'] ?? 'دسته‌بندی'); ?>
            </h1>
            <?php if (!empty($category['description'])): ?>
                <p class="cat-desc"><?php echo htmlspecialchars($category['description']); ?></p>
            <?php endif; ?>
        </header>

        <!-- زیردسته‌ها -->
        <?php if (!empty($category['children'])): ?>
            <div class="cat-children">
                <?php foreach ($category['children'] as $child): ?>
                    <a href="/category/<?php echo htmlspecialchars($child['slug']); ?>" class="cat-child-chip">
                        <i class="<?php echo htmlspecialchars($child['icon'] ?? 'fas fa-folder'); ?>"></i>
                        <?php echo htmlspecialchars($child['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- لیست مطالب -->
        <?php if (empty($posts)): ?>
            <div class="cat-empty">
                <i class="fas fa-inbox"></i>
                <h3>هنوز مطلبی در این دسته منتشر نشده است</h3>
                <p>به‌زودی مطالب تخصصی در این حوزه منتشر خواهد شد.</p>
                <a href="/" class="cat-btn">بازگشت به صفحه اصلی</a>
            </div>
        <?php else: ?>
            <div class="cat-posts-grid">
                <?php foreach ($posts as $post): ?>
                    <article class="cat-post-card">
                        <?php if (!empty($post['image'])): ?>
                            <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>" class="cat-post-thumb">
                                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>">
                            </a>
                        <?php endif; ?>

                        <div class="cat-post-body">
                            <div class="cat-post-meta">
                                <?php if (!empty($post['created_at'])): ?>
                                    <span><i class="far fa-calendar-alt"></i> <?php echo date('Y/m/d', strtotime($post['created_at'])); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($post['category_name'])): ?>
                                    <span class="cat-chip"><?php echo htmlspecialchars($post['category_name']); ?></span>
                                <?php endif; ?>
                            </div>

                            <h2 class="cat-post-title">
                                <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h2>

                            <?php if (!empty($post['excerpt'])): ?>
                                <p class="cat-post-excerpt"><?php echo htmlspecialchars(mb_substr($post['excerpt'], 0, 160)); ?>…</p>
                            <?php endif; ?>

                            <a href="/post/<?php echo htmlspecialchars($post['slug']); ?>" class="cat-read-more">
                                ادامه مطلب <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>