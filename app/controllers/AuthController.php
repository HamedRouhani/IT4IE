<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Setting;
use App\Helpers\Captcha;

class AuthController extends Controller
{
    /**
     * Show login page
     */
    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }

        $captcha = new Captcha();
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $captchaCode = $_POST['captcha'] ?? '';
            $remember = isset($_POST['remember']) ? true : false;
            
            $errors = [];
            if (empty($email)) {
                $errors[] = 'لطفاً ایمیل خود را وارد کنید.';
            }
            if (empty($password)) {
                $errors[] = 'لطفاً رمز عبور خود را وارد کنید.';
            }
            if (empty($captchaCode)) {
                $errors[] = 'لطفاً کد امنیتی را وارد کنید.';
            }
            
            if (empty($errors) && !$captcha->verify($captchaCode)) {
                $errors[] = 'کد امنیتی اشتباه است.';
            }
            
            if (empty($errors)) {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                
                if ($user && password_verify($password, $user['password'])) {
                    if ($user['is_active'] == 0) {
                        $errors[] = 'حساب کاربری شما غیرفعال است.';
                    } elseif ($user['email_verified'] == 0) {
                        $errors[] = 'ایمیل شما تأیید نشده است.';
                    } else {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_role'] = $user['role'];
                        
                        $userModel->updateLastLogin($user['id']);
                        
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $expires = time() + (86400 * 30);
                            $userModel->update($user['id'], [
                                'remember_token' => $token,
                                'remember_expires' => date('Y-m-d H:i:s', $expires)
                            ]);
                            setcookie('remember_token', $token, $expires, '/', '', false, true);
                        }
                        
                        // هدایت به صفحه قبلی یا صفحه اصلی
                        $redirectUrl = $_SESSION['redirect_after_login'] ?? '/';
                        unset($_SESSION['redirect_after_login']);
                        
                        if ($user['role'] === 'admin' || $user['role'] === 'editor') {
                            $_SESSION['message'] = 'خوش آمدید ' . $user['name'];
                            $this->redirect('/admin');
                        } else {
                            $_SESSION['message'] = 'خوش آمدید ' . $user['name'];
                            $this->redirect($redirectUrl);
                        }
                    }
                } else {
                    $errors[] = 'ایمیل یا رمز عبور اشتباه است.';
                }
            }
        }
        
        $this->renderAuth('auth/login', [
            'title' => 'ورود به حساب کاربری',
            'settings' => $settings,
            'errors' => $errors ?? null
        ]);
    }
    
    /**
     * Show register page
     */
    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        
        $captcha = new Captcha();
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $phone = trim($_POST['phone'] ?? '');
            $captchaCode = $_POST['captcha'] ?? '';
            
            $errors = [];
            
            if (empty($name)) {
                $errors[] = 'لطفاً نام خود را وارد کنید.';
            } elseif (strlen($name) < 3) {
                $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
            }
            
            if (empty($email)) {
                $errors[] = 'لطفاً ایمیل خود را وارد کنید.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'ایمیل معتبر نیست.';
            }
            
            if (empty($password)) {
                $errors[] = 'لطفاً رمز عبور را وارد کنید.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            }
            
            if ($password !== $passwordConfirm) {
                $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند.';
            }
            
            if (!empty($phone)) {
                $phone = preg_replace('/[^0-9]/', '', $phone);
                if (strlen($phone) < 10 || strlen($phone) > 15) {
                    $errors[] = 'شماره تماس معتبر نیست.';
                }
            }
            
            if (empty($captchaCode)) {
                $errors[] = 'لطفاً کد امنیتی را وارد کنید.';
            } elseif (!$captcha->verify($captchaCode)) {
                $errors[] = 'کد امنیتی اشتباه است.';
            }
            
            if (empty($errors)) {
                $userModel = new User();
                if ($userModel->findByEmail($email)) {
                    $errors[] = 'این ایمیل قبلاً ثبت شده است.';
                }
            }
            
            if (empty($errors)) {
                $verificationToken = bin2hex(random_bytes(32));
                
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'phone' => $phone,
                    'role' => 'user',
                    'is_active' => 1,
                    'email_verified' => 0,
                    'verification_token' => $verificationToken,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $userModel = new User();
                $userId = $userModel->create($userData);
                
                if ($userId) {
                    // ارسال ایمیل تأیید
                    $emailSent = sendVerificationEmail($email, $name, $verificationToken);
                    
                    // ذخیره اطلاعات در session برای صفحه انتظار
                    $_SESSION['pending_email'] = $email;
                    $_SESSION['pending_name'] = $name;
                    $_SESSION['email_sent'] = $emailSent;
                    
                    if ($emailSent) {
                        $_SESSION['message'] = '✅ ثبت‌نام با موفقیت انجام شد. لطفاً ایمیل خود را بررسی کنید و روی لینک تأیید کلیک نمایید.';
                    } else {
                        $_SESSION['error'] = '⚠️ ثبت‌نام انجام شد اما ارسال ایمیل با مشکل مواجه شد. لطفاً با پشتیبانی تماس بگیرید.';
                    }
                    
                    // ریدایرکت به صفحه انتظار ایمیل
                    $this->redirect('/verify-email-needed');
                } else {
                    $errors[] = 'خطا در ثبت‌نام. لطفاً مجدداً تلاش کنید.';
                }
            }
        }
        
        $this->renderAuth('auth/register', [
            'title' => 'ثبت‌نام در سایت',
            'settings' => $settings,
            'errors' => $errors ?? null,
            'old' => $_POST ?? []
        ]);
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            if (isset($_SESSION['user_id'])) {
                $userModel = new User();
                $userModel->update($_SESSION['user_id'], [
                    'remember_token' => null,
                    'remember_expires' => null
                ]);
            }
        }
        
        $_SESSION = [];
        session_destroy();
        $this->redirect('/');
    }
    
    /**
     * Show forgot password page
     */
    public function forgot()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        
        $captcha = new Captcha();
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $captchaCode = $_POST['captcha'] ?? '';
            
            $errors = [];
            
            if (empty($email)) {
                $errors[] = 'لطفاً ایمیل خود را وارد کنید.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'ایمیل معتبر نیست.';
            }
            
            if (empty($captchaCode)) {
                $errors[] = 'لطفاً کد امنیتی را وارد کنید.';
            } elseif (!$captcha->verify($captchaCode)) {
                $errors[] = 'کد امنیتی اشتباه است.';
            }
            
            if (empty($errors)) {
                $userModel = new User();
                $user = $userModel->findByEmail($email);
                
                if ($user) {
                    $resetToken = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    $userModel->update($user['id'], [
                        'reset_token' => $resetToken,
                        'reset_token_expires' => $expires
                    ]);
                    
                    // ارسال ایمیل با استفاده از تابع کمکی
                    $result = sendResetEmail($email, $user['name'], $resetToken);
                    
                    if ($result) {
                        $_SESSION['message'] = 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد.';
                    } else {
                        $_SESSION['error'] = 'خطا در ارسال ایمیل. لطفاً مجدداً تلاش کنید.';
                    }
                } else {
                    $_SESSION['message'] = 'اگر این ایمیل در سیستم ثبت شده باشد، لینک بازیابی برای شما ارسال خواهد شد.';
                }
                $this->redirect('/login');
            }
        }
        
        $this->renderAuth('auth/forgot', [
            'title' => 'بازیابی رمز عبور',
            'settings' => $settings,
            'errors' => $errors ?? null
        ]);
    }
    
    /**
     * Reset password with token
     */
    public function reset($token)
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
        }
        
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        $userModel = new User();
        $user = $userModel->findByResetToken($token);
        
        if (!$user || strtotime($user['reset_token_expires']) < time()) {
            $_SESSION['error'] = 'لینک بازیابی نامعتبر یا منقضی شده است.';
            $this->redirect('/forgot-password');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            $errors = [];
            
            if (empty($password)) {
                $errors[] = 'لطفاً رمز عبور جدید را وارد کنید.';
            } elseif (strlen($password) < 6) {
                $errors[] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            }
            
            if ($password !== $passwordConfirm) {
                $errors[] = 'رمز عبور و تکرار آن مطابقت ندارند.';
            }
            
            if (empty($errors)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                $userModel->update($user['id'], [
                    'password' => $hashedPassword,
                    'reset_token' => null,
                    'reset_token_expires' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                $_SESSION['message'] = 'رمز عبور با موفقیت تغییر کرد.';
                $this->redirect('/login');
            }
        }
        
        $this->renderAuth('auth/reset', [
            'title' => 'بازیابی رمز عبور',
            'settings' => $settings,
            'token' => $token,
            'errors' => $errors ?? null
        ]);
    }
    
    /**
     * Verify email with token
     */
    public function verify($token)
    {
        $userModel = new User();
        $user = $userModel->findByVerificationToken($token);
        
        if ($user) {
            $userModel->update($user['id'], [
                'email_verified' => 1,
                'verification_token' => null,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $_SESSION['message'] = 'ایمیل شما با موفقیت تأیید شد.';
        } else {
            $_SESSION['error'] = 'لینک تأیید نامعتبر است.';
        }
        
        $this->redirect('/login');
    }

    /**
     * 🆕 صفحه "ایمیل خود را تأیید کنید"
     */
    public function verifyEmailNeeded()
    {
        // اگر کاربر قبلاً لاگین کرده، به صفحه اصلی برود
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/');
            return;
        }

        $email = $_SESSION['pending_email'] ?? '';
        $name = $_SESSION['pending_name'] ?? 'کاربر گرامی';

        // اگر ایمیلی در سشن نبود (مثلاً سشن پاک شده)، به صفحه ورود برگرد
        if (empty($email)) {
            $this->redirect('/login');
            return;
        }

        // رندر ویو با حداقل وابستگی برای جلوگیری از خطاهای احتمالی
        $this->renderAuth('auth/verify-email-needed', [
            'title' => 'تأیید ایمیل - IT4IE',
            'email' => $email,
            'name' => $name,
            'emailSent' => $_SESSION['email_sent'] ?? true,
        ]);
    }

    /**
     * 🆕 ارسال مجدد ایمیل تأیید
     */
    public function resendVerification()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/verify-email-needed');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['resend_error'] = 'ایمیل معتبر نیست.';
            $this->redirect('/verify-email-needed');
            return;
        }
        
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        
        if (!$user) {
            $_SESSION['resend_error'] = 'کاربری با این ایمیل یافت نشد.';
            $this->redirect('/verify-email-needed');
            return;
        }
        
        if ($user['email_verified']) {
            $_SESSION['resend_error'] = 'این ایمیل قبلاً تأیید شده است.';
            $this->redirect('/login');
            return;
        }
        
        // تولید توکن جدید
        $newToken = bin2hex(random_bytes(32));
        $userModel->update($user['id'], [
            'verification_token' => $newToken,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $sent = sendVerificationEmail($email, $user['name'], $newToken);
        
        if ($sent) {
            $_SESSION['resend_message'] = '✅ ایمیل تأیید مجدداً ارسال شد. Inbox و پوشه Spam را بررسی کنید.';
        } else {
            $_SESSION['resend_error'] = '❌ خطا در ارسال ایمیل. لطفاً با پشتیبانی تماس بگیرید.';
        }
        
        $this->redirect('/verify-email-needed');
    }
}