<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">ورود به حساب کاربری</h1>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/login" class="auth-form">
            <!-- Email -->
            <div class="form-group">
                <label for="email">ایمیل</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com" 
                           value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password">رمز عبور</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required 
                           placeholder="********">
                    <button type="button" class="toggle-password" aria-label="نمایش رمز عبور">
                        <i class="fas fa-eye"></i>
                    </button>
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
            
            <!-- Options -->
            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" value="1">
                    <span class="checkbox-text">مرا به خاطر بسپار</span>
                </label>
                <a href="/forgot-password" class="forgot-link">رمز عبور را فراموش کرده‌اید؟</a>
            </div>
            
            <!-- Submit -->
            <button type="submit" class="btn-auth">
                <i class="fas fa-sign-in-alt"></i>
                ورود
            </button>
        </form>
        
        <div class="auth-footer">
            <p>حساب کاربری ندارید؟ <a href="/register">ثبت‌نام کنید</a></p>
        </div>
    </div>
</div>