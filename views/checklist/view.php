<div class="checklist-view-page">
    <div class="container">
        <a href="/checklist" class="back-link">
            <i class="fas fa-arrow-right"></i> بازگشت به لیست چک‌لیست‌ها
        </a>

        <div class="checklist-header">
            <h1><?= htmlspecialchars($checklist['title']) ?></h1>
            <p><?= htmlspecialchars($checklist['description'] ?? '') ?></p>
            <div class="header-meta">
                <span><i class="fas fa-clock"></i> حدود <?= $checklist['estimated_time'] ?> دقیقه</span>
                <span><i class="fas fa-question-circle"></i> <?= $checklist['questions_count'] ?> سوال</span>
            </div>
        </div>

        <?php if (!$isLoggedIn): ?>
            <div class="login-required">
                <i class="fas fa-lock"></i>
                <h3>برای پر کردن این چک‌لیست باید وارد حساب کاربری شوید</h3>
                <p>با ورود به حساب، می‌توانید نتیجه را ذخیره کرده و در آینده به آن دسترسی داشته باشید.</p>
                <div class="auth-buttons">
                    <a href="/login" class="btn-primary">ورود به حساب</a>
                    <a href="/register" class="btn-secondary">ثبت‌نام رایگان</a>
                </div>
            </div>
        <?php else: ?>
            
            <?php if (!empty($userSubmissions)): ?>
                <div class="already-submitted">
                    <i class="fas fa-check-circle"></i>
                    <strong>شما قبلاً این چک‌لیست را پر کرده‌اید.</strong>
                    <a href="/checklist/history">مشاهده تاریخچه</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="/checklist/submit" id="checklistForm">
                <input type="hidden" name="checklist_id" value="<?= $checklist['id'] ?>">
                
                <div class="company-field">
                    <label>نام شرکت/پروژه (اختیاری)</label>
                    <input type="text" name="company" placeholder="مثال: شرکت نمونه">
                </div>

                <?php foreach ($questions as $category => $catQuestions): ?>
                <div class="question-category">
                    <?php foreach ($catQuestions as $q): ?>
                    <div class="question-item">
                        <p class="question-text"><?= htmlspecialchars($q['question_text']) ?></p>
                        <div class="answer-options">
                            <?php
                            $options = [
                                3 => ['label' => 'بله، به‌شدت', 'desc' => '(ریسک بالا)', 'color' => '#e53e3e'],
                                2 => ['label' => 'تا حدودی', 'desc' => '(ریسک متوسط)', 'color' => '#ed8936'],
                                1 => ['label' => 'به ندرت', 'desc' => '(ریسک کم)', 'color' => '#ecc94b'],
                                0 => ['label' => 'خیر، اصلاً', 'desc' => '(مطلوب)', 'color' => '#48bb78']
                            ];
                            foreach ($options as $val => $opt):
                            ?>
                            <label class="option-item" style="--opt-color: <?= $opt['color'] ?>;">
                                <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $val ?>" required>
                                <span class="option-label"><?= $opt['label'] ?></span>
                                <small><?= $opt['desc'] ?></small>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-chart-pie"></i>
                        مشاهده نتیجه و توصیه‌ها
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>