<!-- Hero Section - داخل content-main و بالای پست‌ها -->
<section class="it4ie-purpose">
    <div class="it4ie-purpose-content">

        <span class="it4ie-eyebrow">
            IT4IE · Industrial Engineering Intelligence
        </span>

        <h1>
            پلتفرم هوشمند حل مسائل مهندسی صنایع
        </h1>

        <p class="it4ie-purpose-text">
            IT4IE با ترکیب دانش مهندسی صنایع، تحلیل داده،
            هوش مصنوعی، بهینه‌سازی و ابزارهای تخصصی،
            به شما کمک می‌کند مسئله خود را بهتر تعریف کنید،
            روش مناسب حل آن را پیدا کنید و به یک تصمیم قابل اجرا برسید.
        </p>

        <div class="it4ie-purpose-tagline">
            مسئله‌ات را بگو؛ مسیر حل آن را پیدا کن.
        </div>

    </div>
</section>


<section class="problem-solver-section">

    <div class="problem-solver-header">

        <span class="problem-solver-badge">
            حل مسئله هوشمند
        </span>

        <h2>
            مسئله‌ات چیست؟
        </h2>

        <p>
            مسئله خود را با زبان ساده توضیح دهید.
            لازم نیست از ابتدا بدانید چه روش یا ابزاری برای حل آن مناسب است.
        </p>

    </div>


    <form id="problemSolverForm" class="problem-solver-form">

        <input
            type="hidden"
            name="_csrf_token"
            value="<?= htmlspecialchars($problemSolverCsrf ?? '', ENT_QUOTES, 'UTF-8') ?>"
        >

        <textarea
            id="problemSolverInput"
            name="problem"
            rows="6"
            maxlength="5000"
            placeholder="مثلاً: می‌خواهم بین چند تأمین‌کننده بهترین گزینه را انتخاب کنم و قیمت، کیفیت و زمان تحویل برایم مهم است..."
            required
        ></textarea>

        <div class="problem-solver-form-footer">

            <span class="problem-solver-hint">
                توضیح مسئله را با زبان خودتان بنویسید.
            </span>

            <button
                type="submit"
                id="problemSolverSubmit"
                class="problem-solver-button"
            >
                <i class="fas fa-wand-magic-sparkles"></i>
                تحلیل اولیه مسئله
            </button>

        </div>

    </form>

    <!-- ============================================
        PROBLEM SOLVER RESULT MODAL
        ============================================ -->
    <div id="problem-solver-modal"
        class="problem-solver-modal"
        aria-hidden="true">

        <div class="problem-solver-modal-backdrop"></div>

        <div class="problem-solver-modal-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="problem-solver-modal-title">

            <button type="button"
                    class="problem-solver-modal-close"
                    id="problem-solver-modal-close"
                    aria-label="بستن">
                ×
            </button>

            <div class="problem-solver-modal-header">

                <div class="problem-solver-success-icon">
                    ✓
                </div>

                <div>
                    <h2 id="problem-solver-modal-title">
                        تحلیل اولیه مسئله
                    </h2>

                    <p>
                        مسیر اولیه حل مسئله شما شناسایی شد.
                    </p>
                </div>

            </div>

            <div class="problem-solver-modal-body">

                <!-- مسئله کاربر -->
                <section class="problem-result-section problem-result-problem">

                    <div class="problem-result-section-title">
                        مسئله شما
                    </div>

                    <div id="problem-result-text"
                        class="problem-result-problem-text">
                    </div>

                </section>


                <!-- حوزه مسئله -->
                <section class="problem-result-section">

                    <div class="problem-result-section-title">
                        حوزه شناسایی‌شده
                    </div>

                    <div id="problem-result-domains"
                        class="problem-result-domains">
                    </div>

                </section>


                <!-- مسیر پیشنهادی -->
                <section class="problem-result-section">

                    <div class="problem-result-section-title">
                        مسیر پیشنهادی حل مسئله
                    </div>

                    <div id="problem-result-path"
                        class="problem-result-path">
                    </div>

                </section>


                <!-- روش‌ها -->
                <section class="problem-result-section">

                    <div class="problem-result-section-title">
                        روش‌های مناسب
                    </div>

                    <div id="problem-result-methods"
                        class="problem-result-tags">
                    </div>

                </section>


                <!-- ابزار پیشنهادی -->
                <section id="problem-result-module-section"
                        class="problem-result-tool">

                    <div class="problem-result-tool-icon">
                        ⚙
                    </div>

                    <div class="problem-result-tool-content">

                        <div class="problem-result-tool-label">
                            ابزار پیشنهادی
                        </div>

                        <div id="problem-result-module-title"
                            class="problem-result-tool-title">
                        </div>

                        <div class="problem-result-tool-description">
                            برای ادامه تحلیل تخصصی مسئله می‌توانید از این ابزار استفاده کنید.
                        </div>

                        <a id="problem-result-module-link"
                        href="#"
                        class="problem-result-tool-button">
                            شروع تحلیل با این ابزار
                            <span>←</span>
                        </a>

                    </div>

                </section>


                <!-- پیام توضیحی -->
                <div class="problem-result-note">
                    <span>ⓘ</span>

                    <p id="problem-result-message">
                        این تحلیل اولیه است و برای انتخاب مسیر مناسب حل مسئله انجام شده است.
                    </p>
                </div>

            </div>

            <div class="problem-solver-modal-footer">

                <button type="button"
                        id="problem-solver-modal-close-footer"
                        class="problem-solver-modal-secondary-button">
                    بازگشت به صفحه
                </button>

            </div>

        </div>
    </div>

    <div
        id="problemSolverLoading"
        class="problem-solver-loading"
        hidden
    >
        <i class="fas fa-spinner fa-spin"></i>
        در حال تحلیل اولیه مسئله...
    </div>

</section>


<section class="it4ie-ecosystem-section">

    <div class="it4ie-ecosystem-header">
        <h2>اکوسیستم حل مسئله IT4IE</h2>
        <p>
            ابزارهای تخصصی IT4IE در مراحل مختلف حل مسئله
            در کنار یکدیگر قرار می‌گیرند.
        </p>
    </div>

    <div class="it4ie-ecosystem-grid">

        <div class="ecosystem-card">
            <i class="fas fa-comments"></i>
            <h3>درک مسئله</h3>
            <p>
                تحلیل کسب‌وکار و نیازمندی‌ها
            </p>
        </div>

        <div class="ecosystem-card">
            <i class="fas fa-scale-balanced"></i>
            <h3>تصمیم‌گیری</h3>
            <p>
                انتخاب و ارزیابی گزینه‌ها
            </p>
        </div>

        <div class="ecosystem-card">
            <i class="fas fa-chart-line"></i>
            <h3>تحلیل داده</h3>
            <p>
                تحلیل آماری و استخراج الگو
            </p>
        </div>

        <div class="ecosystem-card">
            <i class="fas fa-gears"></i>
            <h3>بهینه‌سازی</h3>
            <p>
                تحقیق در عملیات و بهینه‌سازی
            </p>
        </div>

        <div class="ecosystem-card">
            <i class="fas fa-diagram-project"></i>
            <h3>مدیریت پروژه</h3>
            <p>
                برنامه‌ریزی، کنترل و تحلیل پروژه
            </p>
        </div>

    </div>

</section>

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
                    <a href="<?= htmlspecialchars($settings['instagram_url'] ?? 'https://instagram.com/it4ieir') ?>"
                       target="_blank" rel="noopener noreferrer" class="social-btn instagram-btn">
                        <div class="social-icon-wrapper">
                            <i class="fab fa-instagram"></i>
                        </div>
                        <div class="social-info">
                            <span class="social-label">اینستاگرام</span>
                            <span class="social-handle">@it4ieir</span>
                        </div>
                        <i class="fas fa-arrow-left social-arrow"></i>
                    </a>

                    <!-- تلگرام - با باز شدن خودکار در اپلیکیشن موبایل -->
                    <a href="https://t.me/IT4IEIR"
                       onclick="handleTelegramClick(event, 'IT4IEIR')"
                       target="_blank" rel="noopener noreferrer" class="social-btn telegram-btn">
                        <div class="social-icon-wrapper">
                            <i class="fab fa-telegram-plane"></i>
                        </div>
                        <div class="social-info">
                            <span class="social-label">تلگرام</span>
                            <span class="social-handle">@IT4IEIR</span>
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

<script>
// تشخیص موبایل و باز کردن تلگرام در اپلیکیشن
function handleTelegramClick(event, username) {
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    if (isMobile) {
        event.preventDefault();
        // تلاش برای باز کردن اپلیکیشن تلگرام
        window.location.href = 'tg://resolve?domain=' + username;
        
        // اگر اپلیکیشن نصب نباشد، بعد از ۲ ثانیه به نسخه وب منتقل می‌شود
        setTimeout(function() {
            window.location.href = 'https://t.me/' + username;
        }, 2000);
    }
    // در دسکتاپ همان لینک عادی کار می‌کند
}
</script>
<script src="/assets/js/problem-solver.js"></script>