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
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        $this->renderAuth('pages/about', [
            'title' => 'درباره ما - IT4IE',
            'settings' => $settings,
            'hideSidebar' => true  // مخفی کردن سایدبار
        ]);
    }
    
    public function contact()
    {
        $settingModel = new Setting();
        $messageModel = new Message();
        
        $settings = $settingModel->getAll();
        
        $userMessages = [];
        if (isset($_SESSION['user_id'])) {
            $userMessages = $messageModel->getUserMessages($_SESSION['user_id']);
        }
        
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            
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
                    'user_id' => $_SESSION['user_id'] ?? null,
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'status' => 'unread'
                ];
                
                $messageModel = new Message();
                $result = $messageModel->create($data);
                
                if ($result) {
                    $_SESSION['message'] = '✅ پیام شما با موفقیت ارسال شد.';
                    $this->redirect('/contact');
                } else {
                    $errors[] = '❌ خطا در ارسال پیام. لطفاً مجدداً تلاش کنید.';
                }
            }
        }
        
        $this->renderAuth('pages/contact', [
            'title' => 'تماس با ما - IT4IE',
            'settings' => $settings,
            'errors' => $errors ?? null,
            'userMessages' => $userMessages,
            'hideSidebar' => true
        ]);
    }
}