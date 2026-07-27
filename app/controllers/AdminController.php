<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Setting;
use App\Models\Message;
use App\Models\User;

class AdminController extends Controller
{
    public function __construct()
    {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
    }
    
    public function dashboard()
    {
        $postModel = new Post();
        $messageModel = new Message();
        $userModel = new User();
        
        $totalPosts = count($postModel->findAll());
        $totalMessages = count($messageModel->findAll(['status' => 'unread']));
        $totalUsers = count($userModel->findAll(['is_active' => 1]));
        $recentPosts = $postModel->findAll([], 'created_at DESC', 5);
        $recentMessages = $messageModel->findAll([], 'created_at DESC', 5);
        
        $this->renderAdmin('admin/dashboard', [
            'title' => 'پنل مدیریت - IT4IE',
            'totalPosts' => $totalPosts,
            'totalMessages' => $totalMessages,
            'totalUsers' => $totalUsers,
            'recentPosts' => $recentPosts,
            'recentMessages' => $recentMessages
        ]);
    }
    
    public function posts()
    {
        $postModel = new Post();
        $categoryModel = new Category();
        
        $posts = $postModel->findAll([], 'created_at DESC');
        $categories = $categoryModel->getAllActive();
        
        $this->renderAdmin('admin/posts', [
            'title' => 'مدیریت پست‌ها - IT4IE',
            'posts' => $posts,
            'categories' => $categories
        ]);
    }
    
    public function createPost()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $slug = $this->generateSlug(trim($_POST['slug'] ?? ''));
            $summary = trim($_POST['summary'] ?? '');
            $content = $_POST['content'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            $status = $_POST['status'] ?? 'draft';
            
            if (empty($slug)) {
                $slug = $this->generateSlug($title);
            }
            
            $errors = [];
            if (strlen($title) < 5) {
                $errors[] = 'عنوان باید حداقل ۵ کاراکتر باشد.';
            }
            if (empty($content)) {
                $errors[] = 'محتوا نمی‌تواند خالی باشد.';
            }
            
            $postModel = new Post();
            $existingPost = $postModel->findBySlug($slug);
            if ($existingPost) {
                $slug = $slug . '-' . time();
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'content' => $content,
                    'category_id' => $category_id,
                    'author_id' => $_SESSION['user_id'],
                    'status' => $status,
                    'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null
                ];
                
                $result = $postModel->create($data);
                
                if ($result) {
                    $_SESSION['message'] = 'پست با موفقیت ایجاد شد.';
                    $this->redirect('/admin/posts');
                } else {
                    $errors[] = 'خطا در ایجاد پست. لطفاً مجدداً تلاش کنید.';
                }
            }
        }
        
        $this->renderAdmin('admin/post_form', [
            'title' => 'ایجاد پست جدید - IT4IE',
            'categories' => $categories,
            'errors' => $errors ?? null,
            'post' => null
        ]);
    }
    
    public function editPost($id)
    {
        $postModel = new Post();
        $categoryModel = new Category();
        
        $post = $postModel->find($id);
        if (!$post) {
            $_SESSION['error'] = 'پست مورد نظر یافت نشد.';
            $this->redirect('/admin/posts');
        }
        
        $categories = $categoryModel->getAllActive();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? '');
            $slug = $this->generateSlug(trim($_POST['slug'] ?? ''));
            $summary = trim($_POST['summary'] ?? '');
            $content = $_POST['content'] ?? '';
            $category_id = $_POST['category_id'] ?? null;
            $status = $_POST['status'] ?? 'draft';
            
            if (empty($slug)) {
                $slug = $this->generateSlug($title);
            }
            
            $errors = [];
            if (strlen($title) < 5) {
                $errors[] = 'عنوان باید حداقل ۵ کاراکتر باشد.';
            }
            if (empty($content)) {
                $errors[] = 'محتوا نمی‌تواند خالی باشد.';
            }
            
            $existingPost = $postModel->findBySlug($slug);
            if ($existingPost && $existingPost['id'] != $id) {
                $slug = $slug . '-' . time();
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => $title,
                    'slug' => $slug,
                    'summary' => $summary,
                    'content' => $content,
                    'category_id' => $category_id,
                    'status' => $status,
                    'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $result = $postModel->update($id, $data);
                
                if ($result) {
                    $_SESSION['message'] = 'پست با موفقیت به‌روزرسانی شد.';
                    $this->redirect('/admin/posts');
                } else {
                    $errors[] = 'خطا در به‌روزرسانی پست. لطفاً مجدداً تلاش کنید.';
                }
            }
        }
        
        $this->renderAdmin('admin/post_form', [
            'title' => 'ویرایش پست - IT4IE',
            'post' => $post,
            'categories' => $categories,
            'errors' => $errors ?? null
        ]);
    }
    
    public function deletePost($id)
    {
        $postModel = new Post();
        $result = $postModel->delete($id);
        
        if ($result) {
            $_SESSION['message'] = 'پست با موفقیت حذف شد.';
        } else {
            $_SESSION['error'] = 'خطا در حذف پست.';
        }
        
        $this->redirect('/admin/posts');
    }
    
    public function messages()
    {
        $messageModel = new Message();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'reply' && isset($_POST['message_id']) && isset($_POST['reply_content'])) {
                $messageId = (int)$_POST['message_id'];
                $replyContent = trim($_POST['reply_content']);
                
                if (!empty($replyContent)) {
                    $result = $messageModel->addReply($messageId, $_SESSION['user_id'], $replyContent);
                    if ($result) {
                        $_SESSION['message'] = 'پاسخ با موفقیت ارسال شد.';
                    } else {
                        $_SESSION['error'] = 'خطا در ارسال پاسخ.';
                    }
                } else {
                    $_SESSION['error'] = 'متن پاسخ نمی‌تواند خالی باشد.';
                }
                $this->redirect('/admin/messages');
            }
            
            if ($action === 'change_status' && isset($_POST['message_id'])) {
                $messageId = (int)$_POST['message_id'];
                $status = $_POST['status'] ?? 'read';
                $messageModel->update($messageId, ['status' => $status]);
                $_SESSION['message'] = 'وضعیت پیام به‌روزرسانی شد.';
                $this->redirect('/admin/messages');
            }
        }
        
        // دریافت پیام‌های اصلی (بدون parent_id)
        $messages = $messageModel->getMessagesWithReplies();
        
        $this->renderAdmin('admin/messages', [
            'title' => 'مدیریت پیام‌ها - IT4IE',
            'messages' => $messages
        ]);
    }
    
    public function settings()
    {
        $settingModel = new Setting();
        $settings = $settingModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            foreach ($_POST as $key => $value) {
                if ($key !== 'submit') {
                    $settingModel->updateByKey($key, trim($value));
                }
            }
            $_SESSION['message'] = 'تنظیمات با موفقیت ذخیره شد.';
            $this->redirect('/admin/settings');
        }
        
        $this->renderAdmin('admin/settings', [
            'title' => 'تنظیمات سایت - IT4IE',
            'settings' => $settings
        ]);
    }
    
    private function generateSlug($string)
    {
        $string = trim($string);
        $string = preg_replace('/[^a-zA-Z0-9_\u0600-\u06FF]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        return strtolower(trim($string, '-'));
    }
}