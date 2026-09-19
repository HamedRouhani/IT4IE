<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Setting;

class BillingController extends Controller
{
    private $subModel;
    private $payModel;

    public function __construct()
    {
        $this->subModel = new Subscription();
        $this->payModel = new Payment();
    }

    // ============================================
    // سمت کاربر
    // ============================================

    /** صفحه تعرفه‌ها */
    public function pricing()
    {
        $current = isset($_SESSION['user_id'])
            ? $this->subModel->getActiveSubscription((int)$_SESSION['user_id'])
            : null;

        $this->render('pages/pricing', [
            'title'      => 'تعرفه‌ها و بسته‌ها - IT4IE',
            'settings'   => (new Setting())->getAll(),
            'plans'      => $this->subModel->getAllPlans(),
            'currentSub' => $current,
            'hideSidebar' => true,
        ]);
    }

    /** شروع خرید: ساخت اشتراک pending + رکورد پرداخت دستی */
    public function subscribe($planId)
    {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'برای خرید اشتراک ابتدا وارد شوید.';
            $this->redirect('/login');
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pricing');
            return;
        }

        $plan = $this->subModel->getPlan((int)$planId);
        if (!$plan || (int)$plan['is_active'] !== 1) {
            $_SESSION['error'] = 'طرح انتخابی یافت نشد.';
            $this->redirect('/pricing');
            return;
        }

        $period = (($_POST['period'] ?? 'monthly') === 'yearly') ? 'yearly' : 'monthly';
        $amount = ($period === 'yearly') ? (int)$plan['price_yearly'] : (int)$plan['price_monthly'];

        // طرح رایگان: فعال‌سازی آنی
        if ($amount <= 0) {
            $subId = $this->subModel->createPending((int)$_SESSION['user_id'], (int)$plan['id'], $period, 0);
            if ($subId) {
                $this->subModel->markActive($subId, null, '0');
                $_SESSION['message'] = 'طرح رایگان برای شما فعال شد.';
            } else {
                $_SESSION['error'] = 'خطا در فعال‌سازی طرح رایگان.';
            }
            $this->redirect('/billing/my');
            return;
        }

        $subId = $this->subModel->createPending((int)$_SESSION['user_id'], (int)$plan['id'], $period, $amount);
        if (!$subId) {
            $_SESSION['error'] = 'خطا در ایجاد سفارش.';
            $this->redirect('/pricing');
            return;
        }

        $isOrganization = (
            strtolower(trim((string)($plan['slug'] ?? ''))) === 'organization' ||
            strtolower(trim((string)($plan['slug'] ?? ''))) === 'organizational' ||
            trim((string)($plan['name'] ?? '')) === 'سازمانی'
        );

        $paymentId = $this->payModel->createForSubscription(
            (int)$_SESSION['user_id'],
            $subId,
            $amount,
            !$isOrganization
        );
        if (!$paymentId) {
            $_SESSION['error'] = 'خطا در ایجاد رکورد پرداخت.';
            $this->redirect('/pricing');
            return;
        }

        $this->redirect('/billing/pay/' . $paymentId);
    }

    /** صفحه راهنمای واریز + ثبت کد پیگیری */
    public function pay($paymentId)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $payment = $this->payModel->getPayment((int)$paymentId);
        if (!$payment || (int)$payment['user_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = 'سفارش پرداخت یافت نشد.';
            $this->redirect('/pricing');
            return;
        }

        $this->render('billing/pay', [
            'title'    => 'تکمیل پرداخت - IT4IE',
            'settings' => (new Setting())->getAll(),
            'payment'  => $payment,
            'hideSidebar' => true,
            'hideFooter'  => true,
        ]);
    }

    /** ثبت کد پیگیری توسط کاربر */
    public function submitPayment()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pricing');
            return;
        }

        $paymentId = (int)($_POST['payment_id'] ?? 0);
        $ref = trim($_POST['ref_code'] ?? '');

        $payment = $this->payModel->getPayment($paymentId);
        if (!$payment || (int)$payment['user_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = 'سفارش پرداخت یافت نشد.';
            $this->redirect('/pricing');
            return;
        }

        $refDigits = preg_replace('/\D/', '', $ref);
        if (strlen($refDigits) < 8 || strlen($refDigits) > 20) {
            $_SESSION['error'] = 'کد پیگیری باید شامل ۸ تا ۲۰ رقم باشد.';
            $this->redirect('/billing/pay/' . $paymentId);
            return;
        }

        $this->payModel->submitRef($paymentId, $refDigits, trim($_POST['payer_card'] ?? ''), trim($_POST['note'] ?? ''));

        $_SESSION['message'] = '✅ پرداخت شما ثبت شد و در صف بررسی است؛ معمولاً زیر ۲ ساعت فعال می‌شود.';
        $this->redirect('/billing/my');
    }

    /** فعال‌سازی با کد اشتراک */
    public function redeem()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pricing');
            return;
        }

        $res = $this->payModel->redeemVoucher(trim($_POST['code'] ?? ''), (int)$_SESSION['user_id'], $this->subModel);
        if ($res['ok']) $_SESSION['message'] = $res['msg'];
        else $_SESSION['error'] = $res['msg'];

        $this->redirect('/billing/my');
    }

    /** درخواست پیش‌فاکتور سازمانی */
    public function requestInvoice()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/pricing');
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $planId = (int)($_POST['plan_id'] ?? 0);

        $company = trim($_POST['company'] ?? '');
        $nationalId = trim($_POST['national_id'] ?? '');
        $contact = trim($_POST['contact'] ?? '');

        if ($planId <= 0) {
            $_SESSION['error'] = 'طرح اشتراک انتخاب نشده است.';
            $this->redirect('/pricing');
            return;
        }

        if ($company === '' || $nationalId === '' || $contact === '') {
            $_SESSION['error'] = 'لطفاً اطلاعات سازمان را کامل وارد کنید.';
            $this->redirect('/pricing');
            return;
        }

        $plan = $this->subModel->getPlan($planId);

        if (!$plan || (int)$plan['is_active'] !== 1) {
            $_SESSION['error'] = 'طرح انتخاب‌شده در دسترس نیست.';
            $this->redirect('/pricing');
            return;
        }

        /*
        * پیش‌فاکتور سازمانی در حال حاضر سالانه است.
        * مبلغ در Subscription ذخیره می‌شود تا اگر قیمت طرح
        * بعداً تغییر کرد، مبلغ این درخواست تغییر نکند.
        */
        $period = 'yearly';
        $amount = (int)$plan['price_yearly'];

        if ($amount <= 0) {
            $_SESSION['error'] = 'برای این طرح امکان صدور پیش‌فاکتور سازمانی وجود ندارد.';
            $this->redirect('/pricing');
            return;
        }

        /*
        * ابتدا یک اشتراک pending ایجاد می‌کنیم.
        * این رکورد هنوز فعال نیست و فقط به عنوان مرجع
        * پیش‌فاکتور/خرید استفاده می‌شود.
        */
        $subId = $this->subModel->createPending(
            $userId,
            (int)$plan['id'],
            $period,
            $amount
        );

        if (!$subId) {
            $_SESSION['error'] = 'خطا در ایجاد درخواست اشتراک سازمانی.';
            $this->redirect('/pricing');
            return;
        }

        $note = 'شرکت: ' . $company .
                ' | شناسه ملی: ' . $nationalId .
                ' | تماس: ' . $contact .
                ' | طرح: ' . ($plan['name'] ?? '') .
                ' | دوره: سالانه';

        $paymentId = $this->payModel->createInvoiceRequest(
            $userId,
            $subId,
            $amount,
            $note
        );

        if (!$paymentId) {
            /*
            * اگر Payment ساخته نشد، Subscription pending
            * باقی می‌ماند ولی فعال نمی‌شود.
            */
            $_SESSION['error'] = 'خطا در ثبت درخواست پیش‌فاکتور.';
            $this->redirect('/pricing');
            return;
        }

        $_SESSION['message'] =
            '📄 درخواست پیش‌فاکتور طرح «' .
            ($plan['name'] ?? '') .
            '» با موفقیت ثبت شد؛ حداکثر تا یک روز کاری با شما تماس می‌گیریم.';

        $this->redirect('/billing/my');
    }

    /** اشتراک و پرداخت‌های من */
    public function my()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $this->render('profile/billing', [
            'title'      => 'اشتراک و پرداخت‌های من - IT4IE',
            'settings'   => (new Setting())->getAll(),
            'currentSub' => $this->subModel->getActiveSubscription((int)$_SESSION['user_id']),
            'limit'      => $this->subModel->getMonthlyChecklistLimit((int)$_SESSION['user_id']),
            'used'       => $this->subModel->countMonthlySubmissions((int)$_SESSION['user_id']),
            'payments'   => $this->payModel->getUserPayments((int)$_SESSION['user_id']),
            'hideSidebar' => true,
            'hideFooter'  => true,
        ]);
    }

    // ============================================
    // سمت ادمین
    // ============================================

    private function requireAdminAuth()
    {
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo '403 - فقط مدیر اصلی دسترسی مالی دارد.';
            exit;
        }
    }

    /** لیست پرداخت‌ها */
    public function adminPayments()
    {
        $this->requireAdminAuth();

        $status = trim($_GET['status'] ?? '');
        $this->renderAdmin('admin/payments', [
            'title'        => 'بررسی پرداخت‌ها - پنل مدیریت',
            'payments'     => $this->payModel->getAllPayments($status),
            'statusFilter' => $status,
        ]);
    }

    /** تأیید / رد پرداخت */
    public function reviewPayment($id)
    {
        $this->requireAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/payments');
            return;
        }

        $payment = $this->payModel->getPayment((int)$id);
        if (!$payment) {
            $this->redirect('/admin/payments');
            return;
        }

        if (!in_array($payment['status'] ?? '', ['pending_review', 'awaiting_contact'], true)) {
            $_SESSION['error'] = 'این درخواست قبلاً تعیین تکلیف شده است.';
            $this->redirect('/admin/payments');
            return;
        }

        $action = $_POST['action'] ?? '';
        $adminNote = trim($_POST['admin_note'] ?? '');

        if ($action === 'approve') {

            $this->payModel->setStatus(
                (int)$id,
                'approved',
                (int)$_SESSION['user_id'],
                $adminNote
            );

            if (!empty($payment['subscription_id'])) {

                $refTag = ($payment['method'] ?? '') === 'invoice'
                    ? 'INVOICE:' . (int)$payment['id']
                    : 'CARD:' . ($payment['ref_code'] ?? '');

                $this->subModel->markActive(
                    (int)$payment['subscription_id'],
                    $refTag,
                    $payment['period'] ?? 'monthly'
                );
            }

            if (($payment['method'] ?? '') === 'invoice') {
                $_SESSION['message'] = 'پیش‌فاکتور تأیید و اشتراک سازمانی فعال شد.';
            } else {
                $_SESSION['message'] = 'پرداخت تأیید و اشتراک فعال شد.';
            }

        }

        $this->redirect('/admin/payments');
    }

    /** مدیریت کدهای اشتراک */
    public function adminVouchers()
    {
        $this->requireAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codes = $this->payModel->generateVouchers(
                (int)($_POST['plan_id'] ?? 2),
                (($_POST['period'] ?? 'monthly') === 'yearly') ? 'yearly' : 'monthly',
                (int)($_POST['count'] ?? 1),
                (int)$_SESSION['user_id'],
                (int)($_POST['expire_days'] ?? 90)
            );
            $_SESSION['message'] = count($codes) . ' کد تولید شد: ' . implode(' ، ', $codes);
            $this->redirect('/admin/vouchers');
            return;
        }

        $this->renderAdmin('admin/vouchers', [
            'title'    => 'کدهای اشتراک - پنل مدیریت',
            'vouchers' => $this->payModel->getVouchers(),
            'plans'    => $this->subModel->getAllPlans(false),
        ]);
    }

    // ============================================
    // مدیریت طرح‌های اشتراک
    // ============================================

    /**
     * لیست طرح‌های اشتراک
     */
    public function adminPlans()
    {
        $this->requireAdminAuth();

        $this->renderAdmin('admin/plans', [
            'title' => 'مدیریت طرح‌های اشتراک - پنل مدیریت',
            'plans' => $this->subModel->getAllPlans(false),
        ]);
    }

    /**
     * ایجاد طرح جدید
     */
    public function createPlan()
    {
        $this->requireAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->renderAdmin('admin/plan-form', [
                'title' => 'ایجاد طرح اشتراک - پنل مدیریت',
                'plan' => null,
                'isEdit' => false,
            ]);
            return;
        }

        $data = $this->getPlanFormData();

        if ($data['name'] === '') {
            $_SESSION['error'] = 'نام طرح الزامی است.';
            $this->redirect('/admin/plans/create');
            return;
        }

        if ($data['slug'] === '') {
            $_SESSION['error'] = 'Slug طرح الزامی است.';
            $this->redirect('/admin/plans/create');
            return;
        }

        if ($this->subModel->planSlugExists($data['slug'])) {
            $_SESSION['error'] = 'این Slug قبلاً استفاده شده است.';
            $this->redirect('/admin/plans/create');
            return;
        }

        if ($data['price_monthly'] < 0 || $data['price_yearly'] < 0) {
            $_SESSION['error'] = 'قیمت نمی‌تواند منفی باشد.';
            $this->redirect('/admin/plans/create');
            return;
        }

        $id = $this->subModel->createPlan($data);

        if ($id > 0) {
            $_SESSION['message'] = 'طرح اشتراک با موفقیت ایجاد شد.';
            $this->redirect('/admin/plans');
            return;
        }

        $_SESSION['error'] = 'خطا در ایجاد طرح اشتراک.';
        $this->redirect('/admin/plans/create');
    }

    /**
     * فرم ویرایش طرح
     */
    public function editPlan($id)
    {
        $this->requireAdminAuth();

        $plan = $this->subModel->getPlan((int)$id);

        if (!$plan) {
            $_SESSION['error'] = 'طرح اشتراک یافت نشد.';
            $this->redirect('/admin/plans');
            return;
        }

        $this->renderAdmin('admin/plan-form', [
            'title' => 'ویرایش طرح اشتراک - پنل مدیریت',
            'plan' => $plan,
            'isEdit' => true,
        ]);
    }

    /**
     * ذخیره تغییرات طرح
     */
    public function updatePlan($id)
    {
        $this->requireAdminAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/plans');
            return;
        }

        $id = (int)$id;

        $plan = $this->subModel->getPlan($id);

        if (!$plan) {
            $_SESSION['error'] = 'طرح اشتراک یافت نشد.';
            $this->redirect('/admin/plans');
            return;
        }

        $data = $this->getPlanFormData();

        if ($data['name'] === '') {
            $_SESSION['error'] = 'نام طرح الزامی است.';
            $this->redirect('/admin/plans/edit/' . $id);
            return;
        }

        if ($data['slug'] === '') {
            $_SESSION['error'] = 'Slug طرح الزامی است.';
            $this->redirect('/admin/plans/edit/' . $id);
            return;
        }

        if ($this->subModel->planSlugExists($data['slug'], $id)) {
            $_SESSION['error'] = 'این Slug قبلاً توسط طرح دیگری استفاده شده است.';
            $this->redirect('/admin/plans/edit/' . $id);
            return;
        }

        if ($data['price_monthly'] < 0 || $data['price_yearly'] < 0) {
            $_SESSION['error'] = 'قیمت نمی‌تواند منفی باشد.';
            $this->redirect('/admin/plans/edit/' . $id);
            return;
        }

        if ($this->subModel->updatePlan($id, $data)) {
            $_SESSION['message'] = 'طرح اشتراک با موفقیت به‌روزرسانی شد.';
            $this->redirect('/admin/plans');
            return;
        }

        $_SESSION['error'] = 'خطا در به‌روزرسانی طرح.';
        $this->redirect('/admin/plans/edit/' . $id);
    }

    /**
     * دریافت و پاک‌سازی اطلاعات فرم طرح
     */
    private function getPlanFormData()
    {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        // فقط حروف انگلیسی، عدد و -
        $slug = strtolower($slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        $description = trim($_POST['description'] ?? '');

        $featuresInput = trim($_POST['features'] ?? '');

        $features = [];

        if ($featuresInput !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $featuresInput);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line !== '') {
                    $features[] = $line;
                }
            }
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'features' => json_encode(
                $features,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'price_monthly' => (int)preg_replace(
                '/\D/',
                '',
                $_POST['price_monthly'] ?? '0'
            ),
            'price_yearly' => (int)preg_replace(
                '/\D/',
                '',
                $_POST['price_yearly'] ?? '0'
            ),
            'checklist_limit_monthly' => max(
                0,
                (int)($_POST['checklist_limit_monthly'] ?? 0)
            ),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
        ];
    }
}