<?php if (!empty($previewMode)): ?>
    <div role="status" style="margin:0 0 20px;padding:14px 18px;border:1px solid #f3d28a;border-radius:10px;background:#fff8e7;color:#744b00;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
        <span><i class="fas fa-eye"></i> پیش‌نمایش خصوصی — این مطلب هنوز برای عموم منتشر نشده است.</span>
        <a href="/admin/posts/edit/<?php echo (int) $post['id']; ?>" style="font-weight:700;color:#6141c8">ویرایش مطلب</a>
    </div>
<?php endif; ?>
<div class="single-post">
    <div class="post-header">
        <h1 class="post-title"><?php echo $post['title']; ?></h1>
        
        <div class="post-meta">
            <span>
                <i class="fas fa-calendar-alt"></i>
                <?php echo jdate($post['published_at'] ?? $post['created_at']); ?>
            </span>
            <?php if (isset($post['category_name']) && !empty($post['category_name'])): ?>
                <span>
                    <i class="fas fa-folder"></i>
                    <a href="/category/<?php echo $post['category_slug'] ?? ''; ?>">
                        <?php echo $post['category_name']; ?>
                    </a>
                </span>
            <?php endif; ?>
            <span>
                <i class="fas fa-eye"></i>
                <?php echo number_format($post['view_count'] ?? 0); ?> بازدید
            </span>
            <span>
                <i class="fas fa-user"></i>
                <?php echo $post['author_name'] ?? 'مدیر'; ?>
            </span>
        </div>
    </div>
    
    <div class="post-content">
        <?php echo $post['content']; ?>
    </div>
    
    <div class="post-footer">
        <a href="/" class="btn-back">
            <i class="fas fa-arrow-right"></i>
            بازگشت به صفحه اصلی
        </a>
    </div>
</div>