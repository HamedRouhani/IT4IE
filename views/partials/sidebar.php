<aside class="sidebar">
    <div class="sidebar-card">
        <h3 class="sidebar-title">
            <i class="fas fa-book-open"></i>
            محتوای تخصصی
        </h3>
        <ul class="sidebar-menu">
            <?php foreach ($categories as $category): ?>
                <li class="sidebar-item <?php echo isset($category['children']) && !empty($category['children']) ? 'has-submenu' : ''; ?>">
                    <a href="/category/<?php echo $category['slug']; ?>" class="sidebar-link">
                        <i class="<?php echo $category['icon'] ?? 'fas fa-folder'; ?>"></i>
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
</aside>