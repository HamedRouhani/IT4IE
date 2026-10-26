<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Models\InventoryMovement;
use App\Software\Inventory\Services\InventoryPolicyService;

final class ExportController extends InventoryController
{
    public function items(): void
    {
        $workspace = $this->workspace();
        $rows = (new InventoryItem())->all((int) $workspace['id']);
        $rows = InventoryPolicyService::classifyAbc($rows);
        $this->csv('inventory-items', ['کد قلم', 'نام قلم', 'دسته', 'واحد', 'موجودی', 'سفارش در راه', 'تقاضای سالانه', 'بهای واحد', 'ارزش مصرف سالانه', 'کلاس ABC'], array_map(static function ($item) {
            return [
                $item['sku'], $item['name'], $item['category'] ?? '', $item['unit'], $item['on_hand'], $item['on_order'],
                $item['annual_demand'], $item['unit_cost'], $item['_analysis']['annual_usage_value'], $item['_analysis']['abc_class'],
            ];
        }, $rows));
    }

    public function analysis(): void
    {
        $workspace = $this->workspace();
        $rows = InventoryPolicyService::classifyAbc((new InventoryItem())->all((int) $workspace['id']));
        $this->csv('inventory-analysis', ['کد قلم', 'نام قلم', 'کلاس ABC', 'موجودی اطمینان', 'نقطه سفارش', 'موقعیت موجودی', 'EOQ', 'مقدار پیشنهادی', 'وضعیت'], array_map(static function ($item) {
            $analysis = $item['_analysis'];
            return [
                $item['sku'], $item['name'], $analysis['abc_class'], $analysis['safety_stock'], $analysis['reorder_point'],
                $analysis['inventory_position'], $analysis['eoq'] ?? '', $analysis['suggested_order'],
                $analysis['should_order'] ? 'نیازمند سفارش' : 'کافی',
            ];
        }, $rows));
    }

    public function movements(): void
    {
        $workspace = $this->workspace();
        $labels = ['opening' => 'موجودی اولیه', 'receipt' => 'دریافت', 'issue' => 'خروج', 'adjustment' => 'تعدیل'];
        $rows = (new InventoryMovement())->all((int) $workspace['id']);
        $this->csv('inventory-movements', ['زمان', 'کد قلم', 'نام قلم', 'نوع گردش', 'تغییر مقدار', 'تسویه سفارش در راه', 'مانده پس از گردش', 'یادداشت'], array_map(static function ($row) use ($labels) {
            return [
                inv_date($row['created_at']), $row['sku'], $row['item_name'],
                $labels[$row['movement_type']] ?? $row['movement_type'], $row['quantity'], $row['on_order_received'] ?? 0, $row['balance_after'], $row['note'] ?? '',
            ];
        }, $rows));
    }

    private function csv(string $name, array $header, array $rows): void
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $name . '-' . date('Ymd') . '.csv"');
        header('X-Content-Type-Options: nosniff');
        $stream = fopen('php://output', 'wb');
        if ($stream === false) throw new \RuntimeException('ساخت فایل خروجی ممکن نشد.');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, $header, ',', '"', '');
        foreach ($rows as $row) {
            $safe = array_map(static function ($value) {
                if (!is_string($value)) return $value;
                if (is_numeric($value)) return $value;
                return preg_match('/^[\x00-\x20]*[=+@-]/u', $value) ? "'" . $value : $value;
            }, $row);
            fputcsv($stream, $safe, ',', '"', '');
        }
        fclose($stream);
        exit;
    }
}
