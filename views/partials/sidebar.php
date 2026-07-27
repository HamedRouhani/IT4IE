<!-- Sidebar - Categories -->
<aside class="sidebar">
    <div class="sidebar-card">
        <h3 class="sidebar-title">
            <i class="fas fa-book-open"></i>
            محتوای تخصصی
        </h3>
        <ul class="sidebar-menu">
            <?php foreach ($categories as $category): ?>
                <li class="sidebar-item">
                    <a href="/category/<?php echo $category['slug']; ?>" class="sidebar-link">
                        <?php if ($category['icon']): ?>
                            <i class="<?php echo $category['icon']; ?>"></i>
                        <?php else: ?>
                            <i class="fas fa-folder"></i>
                        <?php endif; ?>
                        <span><?php echo $category['name']; ?></span>
                        <?php if (isset($category['children']) && !empty($category['children'])): ?>
                            <i class="fas fa-chevron-left sidebar-arrow"></i>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (isset($category['children']) && !empty($category['children'])): ?>
                        <ul class="sidebar-submenu">
                            <?php foreach ($category['children'] as $child): ?>
                                <li>
                                    <a href="/category/<?php echo $child['slug']; ?>" class="sidebar-sub-link">
                                        <i class="fas fa-angle-left"></i>
                                        <?php echo $child['name']; ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Additional sidebar widget (برترین مطالب) -->
    <div class="sidebar-card">
        <h3 class="sidebar-title">
            <i class="fas fa-star"></i>
            برترین مطالب
        </h3>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-angle-left"></i>
                    <span>مطلب نمونه ۱</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="#" class="sidebar-link">
                    <i class="fas fa-angle-left"></i>
                    <span>مطلب نمونه ۲</span>
                </a>
            </li>
        </ul>
    </div>
</aside>