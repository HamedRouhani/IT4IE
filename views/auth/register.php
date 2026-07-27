<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">ثبت‌نام در سایت</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/register" class="auth-form">
            <div class="form-group">
                <label for="name">نام و نام خانوادگی</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="name" name="name" required 
                           placeholder="نام کامل" value="<?php echo $_POST['name'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">ایمیل</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com" value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="phone">شماره تماس (اختیاری)</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input type="tel" id="phone" name="phone" 
                           placeholder="۰۹۱۲۳۴۵۶۷۸۹" value="<?php echo $_POST['phone'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">رمز عبور</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required 
                           placeholder="حداقل ۶ کاراکتر">
                    <button type="button" class="toggle-password" aria-label="نمایش رمز عبور">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-hint">رمز عبور باید حداقل ۶ کاراکتر باشد.</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">تکرار رمز عبور</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-check"></i>
                    </span>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           placeholder="تکرار رمز عبور">
                </div>
            </div>
            
            <!-- Captcha -->
            <div class="form-group captcha-group">
                <label for="captcha">کد امنیتی</label>
                <div class="captcha-container">
                    <div class="captcha-image">
                        <img src="/refresh_captcha.php?<?php echo time(); ?>" 
                            alt="کد امنیتی" 
                            id="captcha-img">
                        <button type="button" class="refresh-captcha" title="بارگذاری مجدد">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="captcha-input">
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-shield-alt"></i>
                            </span>
                            <input type="text" id="captcha" name="captcha" required 
                                placeholder="۴ رقم" 
                                maxlength="4" 
                                autocomplete="off"
                                inputmode="numeric"
                                pattern="[0-9]*">
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fas fa-user-plus"></i>
                ثبت‌نام
            </button>
        </form>
        
        <div class="auth-footer">
            <p>قبلاً ثبت‌نام کرده‌اید؟ <a href="/login">وارد شوید</a></p>
        </div>
    </div>
</div>