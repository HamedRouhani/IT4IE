<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Message;

class PageController extends Controller
{
    public function about()
    {
        $categoryModel = new Category();
        $settingModel = new Setting();
        
        $categories = $categoryModel->getTree();
        $settings = $settingModel->getAll();
        
        $this->render('pages/about', [
            'title' => 'درباره ما - IT4IE',
            'categories' => $categories,
            'settings' => $settings
        ]);
    }
    
    public function contact()
    {
        $settingModel = new Setting();
        $messageModel = new Message();
        
        $settings = $settingModel->getAll();
        
        // ============================================
        // بررسی لاگین بودن کاربر برای ارسال پیام
        // ============================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // اگر کاربر لاگین نبود، به صفحه ورود هدایت کن
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['redirect_after_login'] = '/contact';
                $_SESSION['error'] = 'برای ارسال پیام لطفاً ابتدا وارد حساب کاربری خود شوید.';
                $this->redirect('/login');
                return;
            }
            
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
            $errors = [];
            
            // اعتبارسنجی سمت سرور
            if (strlen($name) < 3) {
                $errors[] = 'نام باید حداقل ۳ کاراکتر باشد.';
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'ایمیل معتبر نیست.';
            }
            if (strlen($subject) < 5) {
                $errors[] = 'موضوع باید حداقل ۵ کاراکتر باشد.';
            }
            if (strlen($message) < 10) {
                $errors[] = 'پیام باید حداقل ۱۰ کاراکتر باشد.';
            }
            
            if (empty($errors)) {
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'subject' => $subject,
                    'message' => $message,
                    'user_id' => $_SESSION['user_id'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'status' => 'unread'
                ];
                
                $result = $messageModel->create($data);
                
                if ($result) {
                    $_SESSION['message'] = '✅ پیام شما با موفقیت ارسال شد.';
                    $this->redirect('/contact');
                } else {
                    $errors[] = '❌ خطا در ارسال پیام. لطفاً مجدداً تلاش کنید.';
                }
            }
        }
        
        // دریافت پیام‌های کاربر در صورت لاگین بودن
        $userMessages = [];
        if (isset($_SESSION['user_id'])) {
            $userMessages = $messageModel->getUserMessages($_SESSION['user_id']);
        }
        
        $this->renderAuth('pages/contact', [
            'title' => 'تماس با ما - IT4IE',
            'settings' => $settings,
            'errors' => $errors ?? null,
            'userMessages' => $userMessages
        ]);
    }
}