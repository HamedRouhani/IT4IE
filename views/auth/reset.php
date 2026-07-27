<div class="auth-container">
    <div class="auth-box">
        <h1 class="auth-title">🔑 تنظیم رمز عبور جدید</h1>
        
        <p class="auth-description">
            لطفاً رمز عبور جدید خود را وارد کنید.
        </p>
        
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
        
        <form method="POST" action="/reset-password/<?php echo $token; ?>" class="auth-form" id="resetForm">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            
            <div class="form-group">
                <label for="password">🔒 رمز عبور جدید</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required 
                           placeholder="حداقل ۶ کاراکتر" minlength="6" maxlength="255">
                    <button type="button" class="toggle-password" aria-label="نمایش رمز عبور">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-hint">رمز عبور باید حداقل ۶ کاراکتر باشد.</small>
            </div>
            
            <div class="form-group">
                <label for="password_confirm">✅ تکرار رمز عبور</label>
                <div class="input-group">
                    <span class="input-icon">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    <input type="password" id="password_confirm" name="password_confirm" required 
                           placeholder="تکرار رمز عبور" minlength="6" maxlength="255">
                </div>
            </div>
            
            <div class="form-options">
                <div class="password-strength" id="passwordStrength">
                    <span class="strength-label">قدرت رمز عبور:</span>
                    <span class="strength-bar">
                        <span class="strength-fill" id="strengthFill"></span>
                    </span>
                    <span class="strength-text" id="strengthText">ضعیف</span>
                </div>
            </div>
            
            <button type="submit" class="btn-auth" id="submitBtn">
                <i class="fas fa-save"></i>
                تغییر رمز عبور
            </button>
        </form>
        
        <div class="auth-footer">
            <p><a href="/login">بازگشت به صفحه ورود</a></p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetForm');
    const submitBtn = document.getElementById('submitBtn');
    const password = document.getElementById('password');
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    // Password strength checker
    password.addEventListener('input', function() {
        const value = this.value;
        let strength = 0;
        let level = 'ضعیف';
        let color = '#ef4444';
        let width = '25%';
        
        if (value.length >= 6) {
            strength += 1;
        }
        if (value.length >= 10) {
            strength += 1;
        }
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) {
            strength += 1;
        }
        if (/\d/.test(value)) {
            strength += 1;
        }
        if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
            strength += 1;
        }
        
        if (strength <= 2) {
            level = 'ضعیف';
            color = '#ef4444';
            width = '25%';
        } else if (strength <= 3) {
            level = 'متوسط';
            color = '#f59e0b';
            width = '50%';
        } else if (strength <= 4) {
            level = 'قوی';
            color = '#22c55e';
            width = '75%';
        } else {
            level = 'بسیار قوی';
            color = '#22c55e';
            width = '100%';
        }
        
        strengthFill.style.width = width;
        strengthFill.style.background = color;
        strengthText.textContent = level;
        strengthText.style.color = color;
    });
    
    form.addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('password_confirm').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('رمز عبور و تکرار آن مطابقت ندارند.');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> در حال تغییر رمز...';
    });
});
</script>