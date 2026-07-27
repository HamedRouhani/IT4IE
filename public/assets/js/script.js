document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // MOBILE MENU TOGGLE
    // ============================================
    const mobileToggle = document.getElementById('mobileToggle');
    const nav = document.getElementById('mainNav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            this.classList.toggle('active');
            nav.classList.toggle('open');
            document.body.classList.toggle('menu-open');
        });
    }
    
    // ============================================
    // MOBILE DROPDOWN TOGGLE
    // ============================================
    const dropdownItems = document.querySelectorAll('.nav-item.has-dropdown');
    
    dropdownItems.forEach(function(item) {
        const link = item.querySelector('.nav-link');
        
        if (link) {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    item.classList.toggle('open');
                }
            });
        }
    });
    
    // ============================================
    // USER DROPDOWN TOGGLE
    // ============================================
    const userBtn = document.getElementById('userDropdownBtn');
    const userMenu = document.getElementById('userDropdownMenu');
    
    if (userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('open');
            userMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (!userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                userBtn.classList.remove('open');
                userMenu.classList.remove('show');
            }
        });
    }
    
    // ============================================
    // CLOSE MENU ON LINK CLICK (Mobile)
    // ============================================
    const navLinks = document.querySelectorAll('.nav-link:not(.has-dropdown .nav-link)');
    
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                if (mobileToggle) mobileToggle.classList.remove('active');
                if (nav) nav.classList.remove('open');
                document.body.classList.remove('menu-open');
            }
        });
    });

    // ============================================
    // SIDEBAR DROPDOWN TOGGLE
    // ============================================
    const sidebarItems = document.querySelectorAll('.sidebar-item');

    sidebarItems.forEach(function(item) {
        const link = item.querySelector('.sidebar-link');
        const submenu = item.querySelector('.sidebar-submenu');
        
        if (submenu && link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                item.classList.toggle('open');
            });
        }
    });
    
    // ============================================
    // TOGGLE PASSWORD VISIBILITY
    // ============================================
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    
    togglePasswordButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');
            
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        });
    });

    // ============================================
    // CAPTCHA REFRESH - راه‌حل جدید
    // ============================================
    const refreshButtons = document.querySelectorAll('.refresh-captcha');
    
    refreshButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // پیدا کردن تصویر کپچا
            const container = this.closest('.captcha-image');
            if (!container) return;
            
            const img = container.querySelector('img');
            if (!img) return;
            
            // اضافه کردن timestamp برای جلوگیری از کش
            const timestamp = new Date().getTime();
            const currentSrc = img.src.split('?')[0];
            img.src = currentSrc + '?' + timestamp;
            
            // انیمیشن بارگذاری
            img.style.opacity = '0.5';
            img.style.transition = 'opacity 0.2s ease';
            
            // بازگشت به حالت عادی بعد از بارگذاری
            setTimeout(function() {
                img.style.opacity = '1';
            }, 300);
            
            // آپدیت مجدد بعد از بارگذاری کامل
            img.onload = function() {
                img.style.opacity = '1';
            };
        });
    });
    
    // بارگذاری اولیه کپچا با timestamp
    const captchaImages = document.querySelectorAll('.captcha-image img');
    captchaImages.forEach(function(img) {
        if (img && !img.src.includes('?')) {
            const timestamp = new Date().getTime();
            img.src = img.src + '?' + timestamp;
        }
    });
});