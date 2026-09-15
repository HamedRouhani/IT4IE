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

        $paymentId = $this->payModel->createForSubscription((int)$_SESSION['user_id'], $subId, $amount);
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

        $plan = $this->subModel->getPlan((int)($_POST['plan_id'] ?? 0));
        if (!$plan) {
            $this->redirect('/pricing');
            return;
        }

        $period = (($_POST['period'] ?? 'yearly') === 'monthly') ? 'monthly' : 'yearly';
        $amount = ($period === 'yearly') ? (int)$plan['price_yearly'] : (int)$plan['price_monthly'];

        $note = 'شرکت: ' . trim($_POST['company'] ?? '') .
                ' | شناسه ملی: ' . trim($_POST['national_id'] ?? '') .
                ' | تماس: ' . trim($_POST['contact'] ?? '');

        $this->payModel->createInvoiceRequest((int)$_SESSION['user_id'], $amount, $note);

        $_SESSION['message'] = '📄 درخواست پیش‌فاکتور ثبت شد؛ حداکثر تا یک روز کاری با شما تماس می‌گیریم.';
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

        $action = $_POST['action'] ?? '';
        $adminNote = trim($_POST['admin_note'] ?? '');

        if ($action === 'approve') {
            $this->payModel->setStatus((int)$id, 'approved', (int)$_SESSION['user_id'], $adminNote);
            if (!empty($payment['subscription_id'])) {
                $this->subModel->markActive((int)$payment['subscription_id'], 'CARD:' . ($payment['ref_code'] ?? ''), $payment['period'] ?? 'monthly');
            }
            $_SESSION['message'] = 'پرداخت تأیید و اشتراک فعال شد.';
        } elseif ($action === 'reject') {
            $this->payModel->setStatus((int)$id, 'rejected', (int)$_SESSION['user_id'], $adminNote);
            if (!empty($payment['subscription_id'])) {
                $this->subModel->markFailed((int)$payment['subscription_id']);
            }
            $_SESSION['message'] = 'پرداخت رد شد.';
        } else {
            $_SESSION['error'] = 'عملیات نامعتبر.';
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
}