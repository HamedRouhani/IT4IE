<header class="header">
    <div class="container">
        <div class="header-wrapper">
            <!-- Logo -->
            <div class="logo">
                <a href="/">
                    <span class="logo-icon">IT</span>
                    <span class="logo-text">4IE</span>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="nav" id="mainNav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="/" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/home') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>خانه</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="/software" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/software') ? 'active' : ''; ?>">
                            <i class="fas fa-cubes"></i>
                            <span>نرم‌افزارها</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="/about" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/about') ? 'active' : ''; ?>">
                            <i class="fas fa-info-circle"></i>
                            <span>درباره ما</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="/contact" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] == '/contact') ? 'active' : ''; ?>">
                            <i class="fas fa-envelope"></i>
                            <span>تماس با ما</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Header Actions - دکمه‌های ورود و ثبت‌نام -->
            <div class="header-actions">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-menu">
                        <button class="user-dropdown-btn" id="userDropdownBtn">
                            <div class="user-avatar">
                                <?php echo substr($_SESSION['user_name'] ?? 'کاربر', 0, 1); ?>
                            </div>
                            <span class="user-name"><?php echo $_SESSION['user_name'] ?? 'کاربر'; ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <a href="/profile" class="dropdown-item">
                                <i class="fas fa-user"></i> پروفایل
                            </a>
                            <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'editor'): ?>
                                <a href="/admin" class="dropdown-item">
                                    <i class="fas fa-tachometer-alt"></i> پنل مدیریت
                                </a>
                            <?php endif; ?>
                            <hr>
                            <a href="/logout" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt"></i> خروج
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="/login" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i> 
                        <span>ورود</span>
                    </a>
                    <a href="/register" class="btn-register">
                        <i class="fas fa-user-plus"></i> 
                        <span>ثبت‌نام</span>
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Mobile Toggle -->
            <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </div>
</header>