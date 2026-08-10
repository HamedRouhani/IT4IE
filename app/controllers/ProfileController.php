<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class ProfileController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * صفحه اصلی پروفایل
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'برای مشاهده پروفایل ابتدا وارد شوید.';
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            $_SESSION['error'] = 'کاربر یافت نشد.';
            $this->redirect('/');
            return;
        }

        $this->renderProfile('profile/index', [
            'title' => 'پروفایل من - ' . $user['name'],
            'user' => $user
        ]);
    }

    /**
     * صفحه ویرایش پروفایل
     */
    public function edit()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'برای ویرایش پروفایل ابتدا وارد شوید.';
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->find($_SESSION['user_id']);
        
        if (!$user) {
            $_SESSION['error'] = 'کاربر یافت نشد.';
            $this->redirect('/');
            return;
        }

        $this->renderProfile('profile/edit', [
            'title' => 'ویرایش پروفایل - ' . $user['name'],
            'user' => $user
        ]);
    }

    /**
     * به‌روزرسانی اطلاعات پروفایل
     */
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'دسترسی غیرمجاز.';
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
            return;
        }

        $userId = $_SESSION['user_id'];
        
        // دریافت و پاکسازی داده‌ها
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'company' => trim($_POST['company'] ?? ''),
            'bio' => trim($_POST['bio'] ?? '')
        ];

        // اعتبارسنجی
        if (empty($data['name'])) {
            $_SESSION['error'] = 'نام الزامی است.';
            $this->redirect('/profile/edit');
            return;
        }

        // پردازش آپلود آواتار
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarPath = $this->uploadAvatar($_FILES['avatar'], $userId);
            if ($avatarPath) {
                $data['avatar'] = $avatarPath;
            } else {
                $_SESSION['error'] = 'خطا در آپلود تصویر پروفایل.';
                $this->redirect('/profile/edit');
                return;
            }
        }

        // به‌روزرسانی در دیتابیس
        $result = $this->userModel->update($userId, $data);

        if ($result) {
            $_SESSION['user_name'] = $data['name'];
            $_SESSION['message'] = 'پروفایل با موفقیت به‌روزرسانی شد.';
        } else {
            $_SESSION['error'] = 'خطا در به‌روزرسانی پروفایل.';
        }

        $this->redirect('/profile');
    }

    /**
     * تغییر رمز عبور
     */
    public function updatePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'دسترسی غیرمجاز.';
            $this->redirect('/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile');
            return;
        }

        $userId = $_SESSION['user_id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // اعتبارسنجی
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'تمام فیلدها الزامی هستند.';
            $this->redirect('/profile/edit');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
            $this->redirect('/profile/edit');
            return;
        }

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
            $this->redirect('/profile/edit');
            return;
        }

        // بررسی رمز عبور فعلی
        $user = $this->userModel->find($userId);
        if (!password_verify($currentPassword, $user['password'])) {
            $_SESSION['error'] = 'رمز عبور فعلی اشتباه است.';
            $this->redirect('/profile/edit');
            return;
        }

        // به‌روزرسانی رمز عبور
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $result = $this->userModel->update($userId, [
            'password' => $hashedPassword,
            'last_password_change' => date('Y-m-d H:i:s')
        ]);

        if ($result) {
            $_SESSION['message'] = 'رمز عبور با موفقیت تغییر کرد.';
        } else {
            $_SESSION['error'] = 'خطا در تغییر رمز عبور.';
        }

        $this->redirect('/profile');
    }

    /**
     * آپلود تصویر آواتار
     */
    private function uploadAvatar($file, $userId)
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        // بررسی نوع فایل
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // بررسی اندازه فایل
        if ($file['size'] > $maxSize) {
            return false;
        }

        // ایجاد مسیر آپلود
        $uploadDir = PUBLIC_PATH . '/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // تولید نام منحصر به فرد
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // انتقال فایل
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/uploads/avatars/' . $filename;
        }

        return false;
    }
}