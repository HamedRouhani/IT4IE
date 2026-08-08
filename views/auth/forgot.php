<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">بازیابی رمز عبور</h1>
        <p class="auth-description">
            ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور برای شما ارسال شود.
        </p>
        
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="/forgot-password" class="auth-form">
            <div class="form-group">
                <label for="email">ایمیل</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="email" name="email" required 
                           placeholder="example@email.com" 
                           value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="form-group captcha-group">
                <label for="captcha">کد امنیتی</label>
                <div class="captcha-container">
                    <div class="captcha-image">
                        <img src="/refresh_captcha.php" alt="کد امنیتی" id="captcha-img">
                        <button type="button" class="refresh-captcha">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="captcha-input">
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-shield-alt"></i></span>
                            <input type="text" id="captcha" name="captcha" required 
                                   placeholder="۴ رقم" maxlength="4">
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fas fa-paper-plane"></i> ارسال لینک بازیابی
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="/login">بازگشت به صفحه ورود</a></p>
        </div>
    </div>
</div>