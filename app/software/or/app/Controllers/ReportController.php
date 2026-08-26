<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;

class ReportController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->requireAuth();
        $this->view('report/index', [
            'pageTitle'   => 'گزارش‌ها',
            'currentPage' => 'report',
            'projects'    => $this->model->getWithType($this->currentUserId),
        ]);
    }

    public function projectReport($id)
    {
        $this->requireAuth();
        $p = $this->model->getWithDetails((int)$id);
        if (!$p) { $this->flashError('پروژه یافت نشد.'); $this->redirect('controller=report'); }
        if (!$this->authorizeOwnership($p['user_id'])) return;

        $this->view('report/project', [
            'pageTitle'    => 'گزارش: ' . $p['name'],
            'currentPage'  => 'report',
            'project'      => $p,
            'sources'      => $this->model->getSources((int)$id),
            'destinations' => $this->model->getDestinations((int)$id),
            'allocations'  => $this->model->getAllocations((int)$id),
            'result'       => $this->model->getResult((int)$id),
            'balance'      => $this->model->getSupplyDemand((int)$id),
        ]);
    }

    public function exportCsv($id)
    {
        $this->requireAuth();
        $p = $this->model->getWithDetails((int)$id);
        if (!$p || !$this->authorizeOwnership($p['user_id'])) return;

        $srcs   = $this->model->getSources((int)$id);
        $dsts   = $this->model->getDestinations((int)$id);
        $allocs = $this->model->getAllocations((int)$id);
        $result = $this->model->getResult((int)$id);

        $sn = array_column($srcs, 'name', 'id');
        $dn = array_column($dsts, 'name', 'id');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="or_report_' . $id . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM برای Excel فارسی

        fputcsv($out, ['گزارش حل مسئله OR - ' . $p['name']]);
        fputcsv($out, ['نوع مسئله', $p['problem_type_name'] ?? '-']);
        fputcsv($out, ['روش حل', $p['method_name'] ?? '-']);
        fputcsv($out, ['هزینه بهینه', $result['total_cost'] ?? '-']);
        fputcsv($out, ['تعداد تکرار', $result['iterations_count'] ?? '-']);
        fputcsv($out, []);
        fputcsv($out, ['مبدأ', 'مقصد', 'مقدار تخصیص', 'هزینه واحد', 'هزینه کل', 'سلول پایه']);

        foreach ($allocs as $a) {
            fputcsv($out, [
                $sn[$a['source_id']] ?? $a['source_id'],
                $dn[$a['destination_id']] ?? $a['destination_id'],
                $a['allocated_amount'],
                $a['unit_cost'],
                $a['total_cost'],
                $a['is_basic_cell'] ? 'بله' : 'خیر',
            ]);
        }

        fclose($out);
        $this->logActivity('export_csv', 'project', (int)$id);
        exit;
    }
}