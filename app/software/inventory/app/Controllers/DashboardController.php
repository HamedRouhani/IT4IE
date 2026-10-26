<?php
namespace App\Software\Inventory\Controllers;

use App\Software\Inventory\Models\InventoryItem;
use App\Software\Inventory\Models\InventoryMovement;
use App\Software\Inventory\Services\InventoryPolicyService;

final class DashboardController extends InventoryController
{
    public function index(): void
    {
        $workspace = $this->workspace();
        $itemModel = new InventoryItem();
        $items = $itemModel->all((int) $workspace['id']);
        $stats = $itemModel->stats((int) $workspace['id']);
        $analysis = InventoryPolicyService::classifyAbc($items);
        $stats['movement_count'] = (new InventoryMovement())->count((int) $workspace['id']);
        $stats['abc_a'] = count(array_filter($analysis, static fn($item) => $item['_analysis']['abc_class'] === 'A'));
        $stats['abc_b'] = count(array_filter($analysis, static fn($item) => $item['_analysis']['abc_class'] === 'B'));
        $stats['abc_c'] = count(array_filter($analysis, static fn($item) => $item['_analysis']['abc_class'] === 'C'));
        $stats['reorder_count'] = count(array_filter($analysis, static fn($item) => $item['_analysis']['should_order']));
        $this->renderPage('dashboard/index', [
            'pageTitle' => 'مدیریت و بهینه‌سازی موجودی',
            'workspace' => $workspace,
            'stats' => $stats,
            'items' => $analysis,
            'movements' => (new InventoryMovement())->latest((int) $workspace['id'], 8),
        ]);
    }
}
