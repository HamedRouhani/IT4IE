<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Models\InventoryPurchaseOrder;
use App\Software\Inventory\Services\InventoryPolicyService;

final class PurchaseOrderController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $model = new InventoryPurchaseOrder();
        $total = $model->count((int) $workspace['id']);
        $pages = max(1, (int) ceil($total / 50));
        $page = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
        $page = max(1, min($pages, (int) ($page ?: 1)));
        $this->renderPage('purchase-order/index', [
            'pageTitle' => 'سفارش‌های خرید',
            'orders' => $model->page((int) $workspace['id'], 50, ($page - 1) * 50),
            'page' => $page,
            'pages' => $pages,
            'totalOrders' => $total,
        ]);
    }

    public function create(): void
    {
        $workspace = $this->workspace();
        $items = (new InventoryItem())->all((int) $workspace['id']);
        $items = InventoryPolicyService::classifyAbc($items);
        $this->renderPage('purchase-order/form', [
            'pageTitle' => 'پیش‌نویس سفارش خرید',
            'items' => $items,
            'suggested' => ($_GET['suggested'] ?? '') === '1',
        ]);
    }

    public function store(): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $supplierRaw = $_POST['supplier_name'] ?? '';
            $expectedRaw = $_POST['expected_date'] ?? '';
            $notesRaw = $_POST['notes'] ?? '';
            if (!is_string($supplierRaw) || !is_string($expectedRaw) || !is_string($notesRaw)) {
                throw new \InvalidArgumentException('اطلاعات سفارش معتبر نیست.');
            }
            $supplier = trim($supplierRaw);
            preg_match_all('/./us', $supplier, $supplierChars);
            if ($supplier === '' || count($supplierChars[0]) > 180) throw new \InvalidArgumentException('نام تأمین‌کننده را وارد کنید (حداکثر ۱۸۰ نویسه).');
            if (preg_match_all('/./us', $notesRaw, $notesChars) > 1000) throw new \InvalidArgumentException('یادداشت سفارش نباید بیشتر از ۱۰۰۰ نویسه باشد.');
            $expectedInput = trim($expectedRaw);
            $expectedDate = null;
            if ($expectedInput !== '') {
                $dateInput = str_replace(['-', '۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['/', '0','1','2','3','4','5','6','7','8','9'], $expectedInput);
                if (!preg_match('/^(?:13\d{2}|14\d{2}|1500)\/\d{1,2}\/\d{1,2}$/D', $dateInput)) {
                    throw new \InvalidArgumentException('تاریخ مورد انتظار را به‌شکل شمسی سال/ماه/روز وارد کنید.');
                }
                $expectedDate = class_exists('App\\Helpers\\DateHelper') ? \App\Helpers\DateHelper::toGregorian($dateInput) : null;
                if (!$expectedDate || \App\Helpers\DateHelper::toJalali($expectedDate, 'Y/n/j') !== self::normalizeJalaliDate($dateInput)) {
                    throw new \InvalidArgumentException('تاریخ شمسی واردشده معتبر نیست.');
                }
            }
            $quantities = $_POST['quantity'] ?? [];
            $costs = $_POST['unit_cost'] ?? [];
            if (!is_array($quantities) || !is_array($costs)) throw new \InvalidArgumentException('ردیف‌های سفارش معتبر نیستند.');
            $lines = [];
            foreach ($quantities as $id => $raw) {
                $id = filter_var($id, FILTER_VALIDATE_INT);
                if (!$id) throw new \InvalidArgumentException('شناسهٔ قلم سفارش نامعتبر است.');
                if (!is_string($raw) && !is_numeric($raw)) throw new \InvalidArgumentException('مقدار سفارش معتبر نیست.');
                $raw = trim((string) $raw);
                if ($raw === '') continue;
                $quantity = $this->decimalValue($raw, 'مقدار سفارش');
                if ($quantity <= 0) throw new \InvalidArgumentException('مقدار هر ردیف سفارش باید بیشتر از صفر باشد.');
                if (count($lines) >= 200) throw new \InvalidArgumentException('هر سفارش حداکثر می‌تواند ۲۰۰ ردیف داشته باشد.');
                $rawCost = $costs[$id] ?? '0';
                if (!is_string($rawCost) && !is_numeric($rawCost)) throw new \InvalidArgumentException('بهای خرید معتبر نیست.');
                $cost = $this->decimalValue((string) $rawCost, 'بهای خرید');
                $lines[] = ['item_id' => (int) $id, 'quantity' => $quantity, 'unit_cost' => $cost];
            }
            $model = new InventoryPurchaseOrder();
            $id = $model->createDraft((int) $workspace['id'], (int) $_SESSION['user_id'], $supplier, $expectedDate, trim($notesRaw) ?: null, $lines);
            inv_flash_set('success', 'پیش‌نویس سفارش خرید ایجاد شد. پس از بررسی، آن را ارسال کنید.');
            $this->redirect(inv_url('purchase_order', 'show', ['id' => $id]));
        } catch (\InvalidArgumentException $e) {
            inv_flash_set('danger', $e->getMessage());
            $this->redirect(inv_url('purchase_order', 'create'));
        } catch (\Throwable $e) {
            error_log('Inventory purchase order create failed: ' . $e->getMessage());
            inv_flash_set('danger', 'ایجاد سفارش انجام نشد؛ اطلاعات اقلام را بررسی کنید.');
            $this->redirect(inv_url('purchase_order', 'create'));
        }
    }

    public function show(int $id): void
    {
        $workspace = $this->workspace();
        $order = (new InventoryPurchaseOrder())->find((int) $workspace['id'], $id);
        if (!$order) {
            http_response_code(404);
            inv_flash_set('danger', 'سفارش خرید پیدا نشد.');
            $this->redirect(inv_url('purchase_order'));
        }
        $this->renderPage('purchase-order/show', ['pageTitle' => 'جزئیات سفارش خرید', 'order' => $order]);
    }

    public function submit(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            (new InventoryPurchaseOrder())->submit((int) $workspace['id'], $id);
            inv_flash_set('success', 'سفارش ارسال شد و مقدار آن به سفارش‌های در راه افزوده شد.');
        } catch (\Throwable $e) {
            inv_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'ارسال سفارش انجام نشد.');
        }
        $this->redirect(inv_url('purchase_order', 'show', ['id' => $id]));
    }

    public function receive(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            $raw = $_POST['received'] ?? [];
            if (!is_array($raw)) throw new \InvalidArgumentException('مقادیر دریافت معتبر نیستند.');
            $receipts = [];
            foreach ($raw as $lineId => $value) {
                $lineId = filter_var($lineId, FILTER_VALIDATE_INT);
                if (!$lineId) throw new \InvalidArgumentException('شناسهٔ ردیف سفارش معتبر نیست.');
                if (!is_string($value) && !is_numeric($value)) throw new \InvalidArgumentException('مقدار دریافت معتبر نیست.');
                $value = trim((string) $value);
                if ($value === '') continue;
                $receipts[(int) $lineId] = $this->decimalValue($value, 'مقدار دریافت');
            }
            (new InventoryPurchaseOrder())->receive((int) $workspace['id'], $id, (int) $_SESSION['user_id'], $receipts);
            inv_flash_set('success', 'دریافت ثبت شد؛ موجودی، سفارش در راه و دفتر گردش به‌روز شدند.');
        } catch (\Throwable $e) {
            inv_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'ثبت دریافت انجام نشد. هیچ تغییری ذخیره نشد.');
        }
        $this->redirect(inv_url('purchase_order', 'show', ['id' => $id]));
    }

    public function cancel(int $id): void
    {
        $workspace = $this->workspace();
        $this->requirePost();
        try {
            (new InventoryPurchaseOrder())->cancel((int) $workspace['id'], $id);
            inv_flash_set('success', 'سفارش لغو شد و ماندهٔ دریافت‌نشده از سفارش در راه کسر شد.');
        } catch (\Throwable $e) {
            inv_flash_set('danger', $e instanceof \InvalidArgumentException ? $e->getMessage() : 'لغو سفارش انجام نشد.');
        }
        $this->redirect(inv_url('purchase_order', 'show', ['id' => $id]));
    }

    private static function normalizeJalaliDate(string $date): string
    {
        $parts = array_map('intval', explode('/', $date));
        return count($parts) === 3 ? $parts[0] . '/' . $parts[1] . '/' . $parts[2] : '';
    }
}
