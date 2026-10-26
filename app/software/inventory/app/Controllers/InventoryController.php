<?php
namespace App\Software\Inventory\Controllers;

use App\Core\Controller;
use App\Software\Inventory\Models\InventoryWorkspace;

abstract class InventoryController extends Controller
{
    protected function workspace(): array
    {
        $this->requireAuth();
        return (new InventoryWorkspace())->getOrCreateForUser((int) $_SESSION['user_id']);
    }

    protected function renderPage(string $view, array $data = []): void
    {
        $this->renderSoftware($view, array_merge([
            'softwareName' => 'Inventory Analyzer',
            'flash' => inv_flash_get(),
            'csrfHtml' => $this->csrfField(),
        ], $data), 'inventory');
    }

    protected function requirePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            $this->redirect(inv_url());
        }
        if (!$this->verifyCsrf()) {
            inv_flash_set('danger', 'درخواست منقضی یا نامعتبر است؛ صفحه را تازه کنید و دوباره تلاش کنید.');
            $this->redirect(inv_url());
        }
    }

    /** Parse bounded decimals using the precision supported by the inventory schema. */
    protected function number(string $field, string $label, float $default = 0.0): float
    {
        $raw = trim((string) ($_POST[$field] ?? $default));
        $scale = $field === 'lead_time_days' ? 2 : ($field === 'holding_rate' ? 6 : 4);
        $precision = $field === 'lead_time_days' ? 10 : ($field === 'holding_rate' ? 8 : 18);
        return $this->decimalValue($raw, $label, $scale, $precision);
    }

    protected function decimalValue(string $raw, string $label, int $scale = 4, int $precision = 18): float
    {
        $raw = str_replace([',', '٬', '٫'], ['', '', '.'], trim($raw));
        if (!preg_match('/^\d+(?:\.\d+)?$/D', $raw)) {
            throw new \InvalidArgumentException("مقدار «{$label}» باید عددی نامنفی باشد.");
        }
        $parts = explode('.', $raw, 2);
        $maxIntegerDigits = $precision - $scale;
        if (strlen($parts[0]) > $maxIntegerDigits || (isset($parts[1]) && strlen($parts[1]) > $scale)) {
            throw new \InvalidArgumentException("مقدار «{$label}» از دقت مجاز بیشتر است؛ حداکثر {$scale} رقم اعشار وارد کنید.");
        }
        $value = (float) $raw;
        if (!is_finite($value)) throw new \InvalidArgumentException("مقدار «{$label}» معتبر نیست.");
        return $value;
    }

    protected function itemPayload(): array
    {
        $sku = trim((string) ($_POST['sku'] ?? ''));
        $name = trim((string) ($_POST['name'] ?? ''));
        $unit = trim((string) ($_POST['unit'] ?? ''));
        if ($sku === '' || $name === '' || $unit === '') throw new \InvalidArgumentException('کد، نام قلم و واحد اندازه‌گیری الزامی است.');
        $holdingRate = $this->number('holding_rate', 'نرخ نگهداری');
        if ($holdingRate > 1) throw new \InvalidArgumentException('نرخ نگهداری باید بین صفر و یک وارد شود؛ مثلاً ۰٫۲۵ برای ۲۵٪.');
        $serviceLevel = (string) ($_POST['service_level'] ?? '95');
        if (!array_key_exists($serviceLevel, \App\Software\Inventory\Services\InventoryPolicyService::serviceLevels())) {
            throw new \InvalidArgumentException('سطح خدمت انتخاب‌شده معتبر نیست.');
        }
        return [
            'sku' => $sku,
            'name' => $name,
            'category' => trim((string) ($_POST['category'] ?? '')) ?: null,
            'unit' => $unit,
            'unit_cost' => $this->number('unit_cost', 'بهای واحد'),
            'on_hand' => $this->number('on_hand', 'موجودی فعلی'),
            'on_order' => $this->number('on_order', 'مقدار سفارش در راه'),
            'annual_demand' => $this->number('annual_demand', 'تقاضای سالانه'),
            'daily_demand_std' => $this->number('daily_demand_std', 'انحراف معیار تقاضای روزانه'),
            'lead_time_days' => $this->number('lead_time_days', 'زمان تأمین'),
            'order_cost' => $this->number('order_cost', 'هزینه هر سفارش'),
            'holding_rate' => $holdingRate,
            'service_level' => $serviceLevel,
        ];
    }
}
