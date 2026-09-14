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

    /**
     * 📋 لیست چک‌لیست‌های ارزیابی ریسک
     */
    public function checklist()
    {
        $this->requireAdmin();

        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();

        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $checklistFilter = isset($_GET['checklist']) ? (int)$_GET['checklist'] : null;

        $submissions = $checklistModel->getAllSubmissions($limit, $offset, $checklistFilter);
        $stats = $checklistModel->getStats($checklistFilter);

        $this->renderAdmin('admin/checklist', [
            'title'           => 'چک‌لیست‌های ارسال شده - پنل مدیریت',
            'submissions'     => $submissions,
            'stats'           => $stats,
            'currentPage'     => $page,
            'checklistFilter' => $checklistFilter,
            'filterChecklist' => $checklistFilter ? $checklistModel->getChecklist($checklistFilter) : null,
        ]);
    }

    /**
     * 📄 جزئیات یک چک‌لیست
     */
    public function viewChecklist($id)
    {
        $this->requireAdmin();

        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();

        $submission = $checklistModel->getSubmission($id);

        if (!$submission) {
            $_SESSION['error'] = 'چک‌لیست یافت نشد';
            $this->redirect('/admin/checklist');
            return;
        }

        $submission['answers_decoded'] = json_decode($submission['answers'], true) ?? [];
        $submission['recommendations_decoded'] = json_decode($submission['recommendations'], true) ?? [];

        $questions = $checklistModel->getActiveQuestions();

        $this->renderAdmin('admin/checklist-view', [
            'title' => 'جزئیات چک‌لیست #' . $id . ' - IT4IE',
            'submission' => $submission,
            'questions' => $questions
        ]);
    }

    /**
     * ✏️ به‌روزرسانی وضعیت پیگیری
     */
    public function updateChecklistStatus()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/checklist');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'new';
        $notes = trim($_POST['notes'] ?? '');

        if ($id > 0) {
            require_once APP_PATH . '/models/Checklist.php';
            $checklistModel = new \App\Models\Checklist();
            $checklistModel->updateStatus($id, $status, $notes);
            $_SESSION['message'] = 'وضعیت با موفقیت به‌روز شد';
        }

        $this->redirect('/admin/checklist/view/' . $id);
    }

    /**
     * 📨 ارسال پیام درون‌برنامه‌ای به کاربر (با استفاده از سیستم موجود پیام‌ها)
     */
    public function messageChecklist($id)
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo "403 - دسترسی غیرمجاز";
            exit;
        }

        require_once APP_PATH . '/models/Checklist.php';
        require_once APP_PATH . '/models/Message.php';
        $checklistModel = new \App\Models\Checklist();
        $messageModel   = new \App\Models\Message();

        $submission = $checklistModel->getSubmission($id);
        if (!$submission) {
            $_SESSION['error'] = 'چک‌لیست یافت نشد';
            $this->redirect('/admin/checklist');
            return;
        }

        // پیام درون‌برنامه‌ای فقط برای ارسال‌هایی که به حساب کاربری متصل‌اند
        if (empty($submission['user_id'])) {
            $_SESSION['error'] = 'این ارسال به حساب کاربری متصل نیست؛ امکان پیام درون‌برنامه‌ای وجود ندارد.';
            $this->redirect('/admin/checklist/view/' . $id);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $subject = trim($_POST['subject'] ?? '');
            $content = trim($_POST['message'] ?? '');

            if (strlen($subject) < 5 || strlen($content) < 10) {
                $_SESSION['error'] = 'موضوع و متن پیام را کامل وارد کنید.';
                $this->redirect('/admin/checklist/message/' . $id);
                return;
            }

            // ۱) ایجاد رشته گفتگو به نام کاربر (تا در صفحه «تماس با ما» او لیست شود)
            $created = $messageModel->create([
                'name'       => $submission['name'],
                'email'      => $submission['email'],
                'subject'    => $subject,
                'message'    => 'گفتگو توسط مدیریت IT4IE درباره ارزیابی چک‌لیست #' . $id . ' آغاز شد.',
                'user_id'    => $submission['user_id'],
                'parent_id'  => null,
                'status'     => 'replied',
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            if ($created) {
                // دریافت ID رشته تازه‌ساخت (همان اتصال PDO، پس LAST_INSERT_ID دقیق است)
                $rows = $messageModel->query("SELECT LAST_INSERT_ID() AS tid");
                $threadId = is_array($rows) ? (int)($rows[0]['tid'] ?? 0) : 0;

                if ($threadId > 0) {
                    // ۲) ثبت پیام مدیر به‌صورت «پاسخ» همان رشته → کاربر با عنوان «پاسخ مدیر» می‌بیند
                    $messageModel->addReply($threadId, $_SESSION['user_id'], $content);

                    // ۳) به‌روزرسانی خودکار وضعیت پیگیری چک‌لیست
                    $checklistModel->updateStatus($id, 'contacted', 'پیام درون‌برنامه‌ای ارسال شد: ' . $subject);

                    $_SESSION['message'] = '✅ پیام ارسال شد و در صفحه «تماس با ما» کاربر نمایش داده می‌شود.';
                    $this->redirect('/admin/checklist/view/' . $id);
                    return;
                }
            }

            $_SESSION['error'] = 'خطا در ارسال پیام.';
            $this->redirect('/admin/checklist/message/' . $id);
            return;
        }

        // نمایش فرم ارسال
        $this->renderAdmin('admin/checklist-message', [
            'title'          => 'ارسال پیام به کاربر - IT4IE',
            'submission'     => $submission,
            'defaultSubject' => 'نتیجه ارزیابی چک‌لیست #' . $id . ' و پیشنهاد مشاوره',
        ]);
    }

        // ============================================
    // مدیریت چک‌لیست‌ها
    // ============================================

    public function checklists()
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        $checklists = $checklistModel->getAllChecklists(true);
        
        $this->renderAdmin('admin/checklists', [
            'title' => 'مدیریت چک‌لیست‌ها',
            'checklists' => $checklists
        ]);
    }

    public function createChecklist()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once APP_PATH . '/models/Checklist.php';
            $checklistModel = new \App\Models\Checklist();
            
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($_POST['title'] ?? '')));
            }
            
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'slug' => $slug,
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? 'general'),
                'icon' => trim($_POST['icon'] ?? 'fa-clipboard-list'),
                'estimated_time' => (int)($_POST['estimated_time'] ?? 5),
                'is_active' => (int)($_POST['is_active'] ?? 1),
                'is_featured' => (int)($_POST['is_featured'] ?? 0),
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            if ($checklistModel->createChecklist($data)) {
                $_SESSION['message'] = 'چک‌لیست با موفقیت ایجاد شد';
            } else {
                $_SESSION['error'] = 'خطا در ایجاد چک‌لیست';
            }
            $this->redirect('/admin/checklists');
            return;
        }
        
        $this->renderAdmin('admin/checklist-form', [
            'title' => 'ایجاد چک‌لیست جدید',
            'checklist' => null
        ]);
    }

    public function editChecklist($id)
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug)) {
                $slug = preg_replace('/[^a-z0-9\-]/', '-', strtolower(trim($_POST['title'] ?? '')));
            }
            
            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'slug' => $slug,
                'description' => trim($_POST['description'] ?? ''),
                'category' => trim($_POST['category'] ?? 'general'),
                'icon' => trim($_POST['icon'] ?? 'fa-clipboard-list'),
                'estimated_time' => (int)($_POST['estimated_time'] ?? 5),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'sort_order' => (int)($_POST['sort_order'] ?? 0)
            ];
            
            if ($checklistModel->updateChecklist($id, $data)) {
                $_SESSION['message'] = 'چک‌لیست با موفقیت به‌روز شد';
            } else {
                $_SESSION['error'] = 'خطا در به‌روزرسانی';
            }
            $this->redirect('/admin/checklists');
            return;
        }
        
        $checklist = $checklistModel->getChecklist($id);
        if (!$checklist) {
            $_SESSION['error'] = 'چک‌لیست یافت نشد';
            $this->redirect('/admin/checklists');
            return;
        }
        
        $this->renderAdmin('admin/checklist-form', [
            'title' => 'ویرایش چک‌لیست - ' . $checklist['title'],
            'checklist' => $checklist
        ]);
    }

    public function deleteChecklist($id)
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        if ($checklistModel->deleteChecklist($id)) {
            $_SESSION['message'] = 'چک‌لیست حذف شد';
        } else {
            $_SESSION['error'] = 'خطا در حذف';
        }
        $this->redirect('/admin/checklists');
    }

    public function checklistQuestions($id)
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        $checklist = $checklistModel->getChecklist($id);
        if (!$checklist) {
            $_SESSION['error'] = 'چک‌لیست یافت نشد';
            $this->redirect('/admin/checklists');
            return;
        }
        
        $questions = $checklistModel->getOrderedQuestions($id);
        
        // حالت ویرایش: ?edit=ID
        $editQuestion = null;
        if (!empty($_GET['edit'])) {
            $editQuestion = $checklistModel->getQuestion((int)$_GET['edit']);
            if ($editQuestion && (int)$editQuestion['checklist_id'] !== (int)$id) {
                $editQuestion = null;
            }
        }
        
        $this->renderAdmin('admin/checklist-questions', [
            'title' => 'مدیریت سوالات - ' . $checklist['title'],
            'checklist' => $checklist,
            'questions' => $questions,
            'editQuestion' => $editQuestion
        ]);
    }

    public function editChecklistQuestion($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/checklists');
            return;
        }
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        $q = $checklistModel->getQuestion($id);
        if (!$q) {
            $_SESSION['error'] = 'سوال یافت نشد';
            $this->redirect('/admin/checklists');
            return;
        }
        
        $data = [
            'category' => trim($_POST['category'] ?? 'general'),
            'question_text' => trim($_POST['question_text'] ?? ''),
            'weight' => (int)($_POST['weight'] ?? 1),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => (int)($_POST['is_active'] ?? 1)
        ];
        
        $checklistModel->updateQuestion($id, $data);
        $_SESSION['message'] = 'سوال با موفقیت به‌روزرسانی شد';
        $this->redirect('/admin/checklist/questions/' . $q['checklist_id']);
    }

    public function moveChecklistQuestion($id)
    {
        $this->requireAdmin();
        
        $dir = $_GET['dir'] ?? 'up';
        $checklistId = (int)($_GET['checklist_id'] ?? 0);
        
        require_once APP_PATH . '/models/Checklist.php';
        $m = new \App\Models\Checklist();
        
        $questions = $m->getOrderedQuestions($checklistId);
        
        // نرمال‌سازی ترتیب‌ها (۰،۱،۲،...)
        foreach ($questions as $i => $q) {
            if ((int)$q['sort_order'] !== $i) {
                $m->setSortOrder($q['id'], $i);
                $questions[$i]['sort_order'] = $i;
            }
        }
        
        // پیدا کردن موقعیت سوال جاری
        $idx = null;
        foreach ($questions as $i => $q) {
            if ((int)$q['id'] === (int)$id) { $idx = $i; break; }
        }
        
        if ($idx !== null) {
            $target = ($dir === 'up') ? $idx - 1 : $idx + 1;
            if ($target >= 0 && $target < count($questions)) {
                // جابه‌جایی ترتیب دو سوال
                $m->setSortOrder($questions[$idx]['id'], $target);
                $m->setSortOrder($questions[$target]['id'], $idx);
            }
        }
        
        $this->redirect('/admin/checklist/questions/' . $checklistId);
    }

    public function addChecklistQuestion($checklistId)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/checklist/questions/' . $checklistId);
            return;
        }
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        $data = [
            'checklist_id' => (int)$checklistId,
            'category' => trim($_POST['category'] ?? 'general'),
            'question_text' => trim($_POST['question_text'] ?? ''),
            'weight' => (int)($_POST['weight'] ?? 1),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'is_active' => (int)($_POST['is_active'] ?? 1)
        ];
        
        if ($checklistModel->createQuestion($data)) {
            $_SESSION['message'] = 'سوال اضافه شد';
        } else {
            $_SESSION['error'] = 'خطا در افزودن سوال';
        }
        $this->redirect('/admin/checklist/questions/' . $checklistId);
    }

    public function deleteChecklistQuestion($id)
    {
        $this->requireAdmin();
        
        require_once APP_PATH . '/models/Checklist.php';
        $checklistModel = new \App\Models\Checklist();
        
        $checklistId = $_GET['checklist_id'] ?? 0;
        
        if ($checklistModel->deleteQuestion($id)) {
            $_SESSION['message'] = 'سوال حذف شد';
        }
        $this->redirect('/admin/checklist/questions/' . $checklistId);
    }

    /**
     * 📊 مشاهده صفحه نتیجه یک ارسال (دید مدیر)
     */
    public function viewChecklistResult($id)
    {
        $this->requireAdmin();

        require_once APP_PATH . '/models/Checklist.php';
        require_once APP_PATH . '/models/Setting.php';
        $checklistModel = new \App\Models\Checklist();
        $settingModel   = new \App\Models\Setting();

        $submission = $checklistModel->getSubmission($id);
        if (!$submission) {
            $_SESSION['error'] = 'ارسال یافت نشد';
            $this->redirect('/admin/checklist');
            return;
        }

        $percentage = $submission['max_score'] > 0
            ? round(($submission['total_score'] / $submission['max_score']) * 100)
            : 0;

        $result = [
            'checklist_title' => $submission['checklist_title'] ?? 'نتایج ارزیابی',
            'checklist_slug'  => $submission['checklist_slug'] ?? '',
            'risk_level'      => $submission['risk_level'],
            'percentage'      => $percentage,
            'total_score'     => $submission['total_score'],
            'max_score'       => $submission['max_score'],
            'recommendations' => json_decode($submission['recommendations'], true) ?? [],
        ];

        $this->render('checklist/result', [
            'title'       => 'نتایج ' . ($submission['checklist_title'] ?? 'ارزیابی') . ' - IT4IE',
            'settings'    => $settingModel->getAll(),
            'result'      => $result,
            'overallRec'  => $checklistModel->getOverallRecommendation($submission['risk_level'], $percentage),
            'resultDate'  => $submission['created_at'],
            'adminView'   => true,
            'hideSidebar' => true,
            'hideFooter'  => true,
        ]);
    }
}