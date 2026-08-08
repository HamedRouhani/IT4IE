<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">تنظیم رمز عبور جدید</h1>
        <p class="auth-description">
            لطفاً رمز عبور جدید خود را وارد کنید.
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
        
        <form method="POST" action="/reset-password/<?php echo $token; ?>" class="auth-form">
            <div class="form-group">
                <label for="password">رمز عبور جدید</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="password" name="password" required 
                           placeholder="حداقل ۶ کاراکتر">
                    <button type="button" class="toggle-password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">تکرار رمز عبور</label>
                <div class="input-group">
                    <span class="input-icon"><i class="fas fa-check"></i></span>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           placeholder="تکرار رمز عبور">
                </div>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fas fa-save"></i> تغییر رمز عبور
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="/login">بازگشت به صفحه ورود</a></p>
        </div>
    </div>
</div>