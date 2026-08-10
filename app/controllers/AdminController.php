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
        
         // 🆕 آمار بازدید سایت
        $todayVisits = 0;
        $totalVisits = 0;
        try {
            require_once APP_PATH . '/models/Visit.php';
            $visitModel = new \App\Models\Visit();
            $visitStats = $visitModel->getOverviewStats();
            $todayVisits = $visitStats['today_visits'] ?? 0;
            $totalVisits = $visitStats['total_visits'] ?? 0;
        } catch (\Exception $e) {
            // خطا در آمار بازدید، داشبورد را متوقف نکند
        }

        $this->renderAdmin('admin/dashboard', [
            'title' => 'پنل مدیریت - IT4IE',
            'totalPosts' => $totalPosts,
            'totalMessages' => $totalMessages,
            'totalUsers' => $totalUsers,
            'recentPosts' => $recentPosts,
            'recentMessages' => $recentMessages,
            'todayVisits' => $todayVisits,
            'totalVisits' => $totalVisits
        ]);
    }
    
    public function posts()
    {
        $postModel = new Post();
        $categoryModel = new Category();
        
        // دریافت پست‌ها با JOIN برای گرفتن نام دسته
        $posts = $postModel->query(
            "SELECT p.*, c.name AS category_name 
             FROM posts p 
             LEFT JOIN categories c ON p.category_id = c.id 
             ORDER BY p.created_at DESC"
        );
        
        $categories = $categoryModel->getAllActive();
        
        $this->renderAdmin('admin/posts', [
            'title' => 'مدیریت پست‌ها - IT4IE',
            'posts' => $posts,
            'categories' => $categories
        ]);
    }
    
        public function createPost()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'editor'])) {
            http_response_code(403); 
            echo "403 - دسترسی غیرمجاز"; 
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title   = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $summary = trim($_POST['summary'] ?? '');
            $status  = $_POST['status'] ?? 'draft';

            // 🛡️ sanitize فیلد category_id
            $categoryId = null;
            if (isset($_POST['category_id']) && $_POST['category_id'] !== '' && is_numeric($_POST['category_id'])) {
                $categoryId = (int) $_POST['category_id'];
            }

            if (empty($title) || empty($content)) {
                $_SESSION['error'] = 'عنوان و متن مطلب الزامی است.';
                $this->redirect('/admin/posts/create'); 
                return;
            }

            // تولید slug
            $slug = trim($_POST['slug'] ?? '') ?: $this->generateSlug($title);

            require_once APP_PATH . '/models/Post.php';
            $postModel = new \App\Models\Post();
            
            // بررسی یکتا بودن slug
            $existingPost = $postModel->findBySlug($slug);
            if ($existingPost) {
                $slug = $slug . '-' . time();
            }

            // 🎯 ذخیره پست با category_id
            $postId = $postModel->create([
                'title'        => $title,
                'slug'         => $slug,
                'summary'      => $summary,
                'content'      => $content,
                'category_id'  => $categoryId,
                'author_id'    => (int) $_SESSION['user_id'],
                'status'       => in_array($status, ['published','draft','archived']) ? $status : 'draft',
                'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

            if ($postId) {
                $_SESSION['message'] = 'مطلب با موفقیت ایجاد شد.';
            } else {
                $_SESSION['error'] = 'خطا در ایجاد مطلب.';
            }
            
            $this->redirect('/admin/posts');
            return;
        }

        // نمایش فرم
        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new \App\Models\Category();

        $this->renderAdmin('admin/post_form', [
            'title'      => 'ایجاد مطلب جدید',
            'categories' => $categoryModel->getAllActive(),
            'post'       => null,
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
            $category_id = (isset($_POST['category_id']) && $_POST['category_id'] !== '' && is_numeric($_POST['category_id']))
                    ? (int) $_POST['category_id'] : null;
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
            'title' => 'ویرایش پست',
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
        // اگر ورودی null یا خالی بود، یک slug پیش‌فرض برگردان
        if (empty($string)) {
            return 'post-' . time();
        }
        
        // تبدیل به حروف کوچک
        $slug = mb_strtolower(trim($string), 'UTF-8');
        
        // جایگزینی کاراکترهای فارسی با معادل انگلیسی
        $persian = ['آ', 'ا', 'ب', 'پ', 'ت', 'ث', 'ج', 'چ', 'ح', 'خ', 'د', 'ذ', 'ر', 'ز', 'ژ', 'س', 'ش', 'ص', 'ض', 'ط', 'ظ', 'ع', 'غ', 'ف', 'ق', 'ک', 'گ', 'ل', 'م', 'ن', 'و', 'ه', 'ی'];
        $english = ['a', 'a', 'b', 'p', 't', 's', 'j', 'ch', 'h', 'kh', 'd', 'z', 'r', 'z', 'zh', 's', 'sh', 's', 'z', 't', 'z', 'a', 'gh', 'f', 'gh', 'k', 'g', 'l', 'm', 'n', 'o', 'h', 'y'];
        $slug = str_replace($persian, $english, $slug);
        
        // جایگزینی فاصله و کاراکترهای خاص با خط تیره
        $slug = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $slug);
        
        // حذف خط تیره‌های تکراری
        $slug = preg_replace('/-+/', '-', $slug);
        
        // حذف خط تیره از ابتدا و انتها
        $slug = trim($slug, '-');
        
        // اگر خالی شد، یک slug پیش‌فرض برگردان
        if (empty($slug)) {
            $slug = 'post-' . time();
        }
        
        return $slug;
    }

    /**
     * 📊 آمار استفاده از نرم‌افزارها
     */
    public function softwareActivity()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        $softwareSlug = $_GET['software'] ?? '';
        $tableName = 'software_activity_logs';

        $logModelFile = APP_PATH . '/models/SoftwareActivityLog.php';
        if (!file_exists($logModelFile)) {
            $this->renderAdmin('admin/software-activity-empty', [
                'title' => 'آمار نرم‌افزارها - IT4IE',
                'error' => 'فایل مدل SoftwareActivityLog یافت نشد.'
            ]);
            return;
        }

        require_once $logModelFile;

        try {
            $logModel = new \App\Models\SoftwareActivityLog();

            // تست وجود جدول (با نام ثابت)
            $logModel->queryOne("SELECT 1 FROM `{$tableName}` LIMIT 1");

            if ($softwareSlug) {
                $logs = $logModel->getBySoftware($softwareSlug, 50, 0);
            } else {
                $logs = $logModel->query(
                    "SELECT l.*, u.name as user_name_from_db, u.email as user_email
                     FROM `{$tableName}` l
                     LEFT JOIN users u ON l.user_id = u.id
                     ORDER BY l.created_at DESC 
                     LIMIT 50"
                );
            }

            $stats = $logModel->getStats($softwareSlug ?: null);
            $statsByAction = $logModel->getStatsByAction($softwareSlug ?: null);

            $this->renderAdmin('admin/software-activity', [
                'title' => 'آمار استفاده از نرم‌افزارها - IT4IE',
                'logs' => $logs,
                'stats' => $stats,
                'statsByAction' => $statsByAction,
                'currentSoftware' => $softwareSlug
            ]);
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), "doesn't exist") !== false) {
                $this->renderAdmin('admin/software-activity-empty', [
                    'title' => 'آمار نرم‌افزارها - IT4IE',
                    'error' => 'جدول `software_activity_logs` در دیتابیس وجود ندارد. لطفاً SQL زیر را در phpMyAdmin اجرا کنید.',
                    'sql' => $this->getTableCreateSQL()
                ]);
            } else {
                throw $e;
            }
        }
    }


    /**
     * 🆕 صفحه مدیریت محدودیت‌های نرم‌افزارها
     */
    public function softwareLimits()
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/SoftwareUsageLimit.php';
        
        $limitModel = new \App\Models\SoftwareUsageLimit();
        
        // دریافت آمار کلی
        $adminStats = $limitModel->getAdminStats();
        
        // ریست محدودیت‌ها در صورت درخواست
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            if ($_POST['action'] === 'reset_all') {
                $limitModel->resetLimits();
                $_SESSION['message'] = 'تمام محدودیت‌ها ریست شدند.';
                $this->redirect('/admin/software-limits');
                return;
            }
            
            if ($_POST['action'] === 'reset_software' && isset($_POST['software_slug'])) {
                $limitModel->resetLimits($_POST['software_slug']);
                $_SESSION['message'] = 'محدودیت‌های نرم‌افزار ' . $_POST['software_slug'] . ' ریست شد.';
                $this->redirect('/admin/software-limits');
                return;
            }
        }
        
        $this->renderAdmin('admin/software-limits', [
            'title' => 'مدیریت محدودیت‌های نرم‌افزارها - پنل مدیریت',
            'adminStats' => $adminStats
        ]);
    }

    /**
     * 🆕 دریافت کلاس badge بر اساس نوع فعالیت
     */
    private function getActionBadgeClass($action)
    {
        $classMap = [
            'enter' => 'success',
            'exit' => 'secondary',
            'create_project' => 'primary',
            'update_project' => 'warning',
            'delete_project' => 'danger',
            'add_task_to_project' => 'primary',
            'remove_task_from_project' => 'danger',
            'update_task_status' => 'warning',
            'add_technique_to_task' => 'primary',
            'remove_technique_from_task' => 'danger',
            'analyze_requirement' => 'success',
            'test_activity' => 'secondary',
            'test_step3' => 'secondary'
        ];
        
        return $classMap[$action] ?? 'secondary';
    }

    /**
     * 📊 صفحه آمار بازدید (فقط ادمین)
     */
    public function visits()
    {
        // فقط ادمین دسترسی دارد
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "<h1>403 - دسترسی غیرمجاز</h1><p>این بخش فقط برای مدیر سایت است.</p>";
            exit;
        }

        require_once APP_PATH . '/models/Visit.php';
        $visitModel = new \App\Models\Visit();

        $this->renderAdmin('admin/visits', [
            'title' => 'آمار بازدید سایت - IT4IE',
            'stats' => $visitModel->getOverviewStats(),
            'daily' => $visitModel->getDailyStats(14),
            'topPages' => $visitModel->getTopPages(10),
            'recent' => $visitModel->getRecentVisits(20),
            'referrers' => $visitModel->getTopReferrers(5)
        ]);
    }

    /**
     * 👥 لیست کاربران
     */
    public function users()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        require_once APP_PATH . '/models/User.php';
        $userModel = new \App\Models\User();
        
        $users = $userModel->query(
            "SELECT id, name, email, phone, role, is_active, email_verified, 
                    company, created_at, last_login 
             FROM users 
             WHERE deleted_at IS NULL 
             ORDER BY created_at DESC"
        );

        $this->renderAdmin('admin/users', [
            'title' => 'مدیریت کاربران - IT4IE',
            'users' => $users
        ]);
    }

    /**
     * 📝 ویرایش کاربر
     */
    public function editUser($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        require_once APP_PATH . '/models/User.php';
        $userModel = new \App\Models\User();
        $user = $userModel->find($id);

        if (!$user) {
            $_SESSION['error'] = 'کاربر یافت نشد.';
            $this->redirect('/admin/users');
            return;
        }

        $this->renderAdmin('admin/user_edit', [
            'title' => 'ویرایش کاربر - ' . $user['name'],
            'user' => $user
        ]);
    }

    /**
     * 💾 به‌روزرسانی کاربر
     */
    public function updateUser()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        $id = (int) ($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $validRoles = ['admin', 'editor', 'client', 'user'];
        if (!in_array($role, $validRoles)) {
            $role = 'user';
        }

        require_once APP_PATH . '/models/User.php';
        $userModel = new \App\Models\User();
        
        $result = $userModel->update($id, [
            'role' => $role,
            'is_active' => $isActive
        ]);

        if ($result) {
            $_SESSION['message'] = 'اطلاعات کاربر با موفقیت به‌روزرسانی شد.';
        } else {
            $_SESSION['error'] = 'خطا در به‌روزرسانی کاربر.';
        }

        $this->redirect('/admin/users');
    }

    /**
     * 🗑️ حذف کاربر (Soft Delete)
     */
    public function deleteUser($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        // جلوگیری از حذف خود مدیر
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'شما نمی‌توانید حساب خود را حذف کنید.';
            $this->redirect('/admin/users');
            return;
        }

        require_once APP_PATH . '/models/User.php';
        $userModel = new \App\Models\User();
        $result = $userModel->softDelete($id);

        if ($result) {
            $_SESSION['message'] = 'کاربر با موفقیت حذف شد.';
        } else {
            $_SESSION['error'] = 'خطا در حذف کاربر.';
        }

        $this->redirect('/admin/users');
    }

        /**
     * 📁 لیست دسته‌بندی‌ها
     */
    public function categories()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'editor'])) {
            http_response_code(403); echo "403 - دسترسی غیرمجاز"; exit;
        }

        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new \App\Models\Category();

        // دریافت همه دسته‌ها با شمارش پست‌ها
        $categories = $categoryModel->query(
            "SELECT c.*, 
                    (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id AND p.status = 'published') as post_count,
                    (SELECT COUNT(*) FROM categories c2 WHERE c2.parent_id = c.id) as child_count
             FROM categories c
             ORDER BY c.parent_id ASC, c.name ASC"
        );

        $this->renderAdmin('admin/categories', [
            'title' => 'مدیریت دسته‌بندی‌ها - IT4IE',
            'categories' => $categories
        ]);
    }

        /**
     * ➕ ایجاد دسته‌بندی جدید
     */
    public function createCategory()
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'editor'])) {
            http_response_code(403); 
            echo "403 - دسترسی غیرمجاز"; 
            exit;
        }

        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new \App\Models\Category();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon        = trim($_POST['icon'] ?? 'fas fa-folder');
            $parentId    = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '' && is_numeric($_POST['parent_id'])) ? (int) $_POST['parent_id'] : null;
            $slug        = trim($_POST['slug'] ?? '') ?: $this->generateSlug($name);

            if (empty($name)) {
                $_SESSION['error'] = 'نام دسته‌بندی الزامی است.';
                $this->redirect('/admin/categories/create'); 
                return;
            }

            // بررسی تکراری نبودن اسلاگ
            $existing = $categoryModel->findBySlug($slug);
            if ($existing) {
                $slug = $slug . '-' . time();
            }

            $categoryModel->create([
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'icon'        => $icon,
                'parent_id'   => $parentId,
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            $_SESSION['message'] = 'دسته‌بندی با موفقیت ایجاد شد.';
            $this->redirect('/admin/categories');
            return;
        }

        // نمایش فرم ایجاد
        $allCategories = $categoryModel->getAllActive();
        $this->renderAdmin('admin/category_form', [
            'title'      => 'ایجاد دسته‌بندی جدید',
            'category'   => null,
            'categories' => $allCategories
        ]);
    }

    /**
     * ✏️ ویرایش دسته‌بندی
     */
    public function editCategory($id)
    {
        if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'] ?? '', ['admin', 'editor'])) {
            http_response_code(403); 
            echo "403 - دسترسی غیرمجاز"; 
            exit;
        }

        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new \App\Models\Category();
        $category = $categoryModel->find($id);

        if (!$category) {
            $_SESSION['error'] = 'دسته‌بندی یافت نشد.';
            $this->redirect('/admin/categories'); 
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name        = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $icon        = trim($_POST['icon'] ?? 'fas fa-folder');
            $parentId    = (isset($_POST['parent_id']) && $_POST['parent_id'] !== '' && is_numeric($_POST['parent_id'])) ? (int) $_POST['parent_id'] : null;
            $slug        = trim($_POST['slug'] ?? '') ?: $this->generateSlug($name);

            // جلوگیری از انتخاب خودِ دسته به عنوان والد
            if ($parentId == $id) {
                $parentId = null;
            }

            // بررسی تکراری نبودن اسلاگ (به جز خودش)
            $existing = $categoryModel->findBySlug($slug);
            if ($existing && $existing['id'] != $id) {
                $slug = $slug . '-' . time();
            }

            $categoryModel->update($id, [
                'name'        => $name,
                'slug'        => $slug,
                'description' => $description,
                'icon'        => $icon,
                'parent_id'   => $parentId,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            $_SESSION['message'] = 'دسته‌بندی با موفقیت به‌روزرسانی شد.';
            $this->redirect('/admin/categories');
            return;
        }

        // نمایش فرم ویرایش
        $allCategories = $categoryModel->getAllActive();
        $this->renderAdmin('admin/category_form', [
            'title'      => 'ویرایش دسته‌بندی',
            'category'   => $category,
            'categories' => $allCategories
        ]);
    }

    /**
     * 🗑️ حذف دسته‌بندی
     */
    public function deleteCategory($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403); echo "403 - دسترسی غیرمجاز"; exit;
        }

        require_once APP_PATH . '/models/Category.php';
        $categoryModel = new \App\Models\Category();

        // بررسی وجود پست مرتبط
        $postCount = $categoryModel->queryOne(
            "SELECT COUNT(*) as cnt FROM posts WHERE category_id = ?",
            [$id]
        );

        if ($postCount && $postCount['cnt'] > 0) {
            $_SESSION['error'] = 'این دسته‌بندی دارای ' . $postCount['cnt'] . ' مطلب است. ابتدا مطالب را به دسته دیگری منتقل کنید.';
            $this->redirect('/admin/categories');
            return;
        }

        // بررسی وجود زیردسته
        $childCount = $categoryModel->queryOne(
            "SELECT COUNT(*) as cnt FROM categories WHERE parent_id = ?",
            [$id]
        );

        if ($childCount && $childCount['cnt'] > 0) {
            $_SESSION['error'] = 'این دسته‌بندی دارای ' . $childCount['cnt'] . ' زیردسته است. ابتدا زیردسته‌ها را حذف یا منتقل کنید.';
            $this->redirect('/admin/categories');
            return;
        }

        $categoryModel->delete($id);
        $_SESSION['message'] = 'دسته‌بندی با موفقیت حذف شد.';
        $this->redirect('/admin/categories');
    }
}