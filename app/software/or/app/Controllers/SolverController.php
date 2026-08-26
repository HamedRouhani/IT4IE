<?php
namespace App\Software\Or\Controllers;
use App\Software\Or\Core\Controller;
use App\Software\Or\Models\Project;
use App\Software\Or\Models\Method;
use App\Software\Or\Helpers\TransportationSolver;
use App\Software\Or\Helpers\AssignmentSolver;

class SolverController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->json(['success'=>false, 'error'=>'Action معتبر نیست.'], 400);
    }

    /**
     * حل مسئله
     * POST /software/or-analyzer/?controller=solver&action=run&id={id}
     * Body: { "method_code": "VAM" }
     */
    public function run($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'فقط POST مجاز است.'], 405);

        $pid = (int)$id;
        $p = $this->model->getWithDetails($pid);
        if (!$p) $this->json(['success'=>false,'error'=>'پروژه یافت نشد.'], 404);
        if (!$this->authorizeOwnership($p['user_id'])) return;

        $raw     = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;
        $mc      = strtoupper($payload['method_code'] ?? $p['method_code'] ?? 'VAM');
        $ptCode  = $p['problem_type_code'] ?? '';

        $result = match ($ptCode) {
            'TRANS', 'TRANSSHIP' => $this->solveTransportation($pid, $p, $mc),
            'ASSIGN'             => $this->solveAssignment($pid, $p),
            default               => ['status'=>'error','message'=>'نوع مسئله پشتیبانی‌نشده: ' . $ptCode],
        };

        if (($result['status'] ?? 'error') === 'success') {
            $this->saveResults($pid, $p, $result, $mc);
            $this->model->updateStatus($pid, 'solved');
            $this->logActivity('solve_' . strtolower($mc), 'project', $pid);
        } else {
            $this->model->updateStatus($pid, 'infeasible');
        }

        $result['success'] = (($result['status'] ?? 'error') === 'success');
        $this->json($result);
    }

    private function solveTransportation(int $pid, array $p, string $mc): array
    {
        $cd     = $this->model->getCostMatrix($pid);
        $srcs   = $cd['sources'];
        $dsts   = $cd['dests'];
        $matrix = $cd['matrix'];

        if (empty($srcs) || empty($dsts))
            return ['status'=>'error','message'=>'ابتدا منابع و مقاصد را تعریف کنید.'];

        if (!$p['is_balanced'])
            return ['status'=>'error','message'=>'مسئله نامتوازن است. ابتدا متوازن‌سازی کنید.'];

        $supply = array_map(fn($s) => (int)$s['capacity'], $srcs);
        $demand = array_map(fn($d) => (int)$d['capacity'], $dsts);

        $result = TransportationSolver::solve($matrix, $supply, $demand, $mc, true);
        if (($result['status'] ?? 'error') !== 'success') return $result;

        // تبدیل به فرمت دیتابیس
        $alloc      = $result['allocation'];
        $dbAllocs   = [];
        $m = count($srcs); $n = count($dsts);

        for ($i = 0; $i < $m; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($alloc[$i][$j] > 0) {
                    $uc = ($matrix[$i][$j] !== null) ? (float)$matrix[$i][$j] : 0;
                    $dbAllocs[] = [
                        'source_id'        => $srcs[$i]['id'],
                        'destination_id'   => $dsts[$j]['id'],
                        'allocated_amount' => $alloc[$i][$j],
                        'unit_cost'        => $uc,
                        'total_cost'       => $alloc[$i][$j] * $uc,
                        'is_basic_cell'    => 1,
                    ];
                }
            }
        }

        $result['db_allocations'] = $dbAllocs;
        $result['source_names']   = array_column($srcs, 'name');
        $result['dest_names']     = array_column($dsts, 'name');
        return $result;
    }

    private function solveAssignment(int $pid, array $p): array
    {
        $cd     = $this->model->getCostMatrix($pid);
        $srcs   = $cd['sources'];
        $dsts   = $cd['dests'];
        $matrix = $cd['matrix'];

        if (empty($srcs) || empty($dsts))
            return ['status'=>'error','message'=>'ابتدا عوامل و وظایف را تعریف کنید.'];

        if (count($srcs) !== count($dsts))
            return ['status'=>'error',
                    'message'=>'ماتریس باید مربع باشد. عوامل: ' . count($srcs) . '، وظایف: ' . count($dsts)];

        $isMin  = ($p['objective'] ?? 'minimize') === 'minimize';
        $result = AssignmentSolver::solve($matrix, $isMin);
        if (($result['status'] ?? 'error') !== 'success') return $result;

        $dbAllocs = [];
        foreach ($result['assignments'] as $a) {
            $i = $a['agent_index']; $j = $a['task_index'];
            $uc = ($matrix[$i][$j] !== null) ? (float)$matrix[$i][$j] : 0;
            $dbAllocs[] = [
                'source_id'        => $srcs[$i]['id'],
                'destination_id'   => $dsts[$j]['id'],
                'allocated_amount' => 1,
                'unit_cost'        => $uc,
                'total_cost'       => $uc,
                'is_basic_cell'    => 1,
            ];
        }

        $result['db_allocations'] = $dbAllocs;
        $result['source_names']   = array_column($srcs, 'name');
        $result['dest_names']     = array_column($dsts, 'name');
        $result['optimal_cost']   = $result['total_cost'];
        $result['iterations']     = 0;
        return $result;
    }

    private function saveResults(int $pid, array $p, array $result, string $mc): void
    {
        if (!empty($result['db_allocations']))
            $this->model->saveAllocations($pid, $result['db_allocations']);

        $totalCost  = $result['optimal_cost'] ?? $result['total_cost'] ?? 0;
        $iterations = $result['iterations'] ?? 0;
        $status     = ($result['has_prohibited'] ?? false) ? 'infeasible' : 'optimal';

        $this->model->saveResult($pid, $totalCost, $iterations, $status);

        if ($mc !== ($p['method_code'] ?? '')) {
            $method = (new Method())->getByCode($mc);
            if ($method) $this->model->update($pid, ['method_id' => $method['id']]);
        }
    }

    /**
     * مقایسه روش‌های حل (فقط حمل و نقل)
     * POST /software/or-analyzer/?controller=solver&action=compare&id={id}
     */
    public function compare($id)
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->json(['success'=>false,'error'=>'فقط POST مجاز است.'], 405);

        $pid = (int)$id;
        $p = $this->model->getWithDetails($pid);
        if (!$p) $this->json(['success'=>false,'error'=>'پروژه یافت نشد.'], 404);
        if (!$this->authorizeOwnership($p['user_id'])) return;

        if (($p['problem_type_code'] ?? '') === 'ASSIGN')
            $this->json(['success'=>false,'error'=>'مقایسه فقط برای حمل و نقل فعال است.']);

        $cd     = $this->model->getCostMatrix($pid);
        $srcs   = $cd['sources'];
        $dsts   = $cd['dests'];
        $matrix = $cd['matrix'];

        $supply = array_map(fn($s) => (int)$s['capacity'], $srcs);
        $demand = array_map(fn($d) => (int)$d['capacity'], $dsts);

        $comparison = [];
        foreach (['NWC', 'LCM', 'VAM'] as $m) {
            $r = TransportationSolver::solve($matrix, $supply, $demand, $m, true);
            if (($r['status'] ?? 'error') === 'success') {
                $comparison[] = [
                    'method'       => $m,
                    'initial_cost' => $r['initial_cost'],
                    'optimal_cost' => $r['optimal_cost'],
                    'iterations'   => $r['iterations'],
                    'improvement'  => ($r['initial_cost'] > 0)
                        ? round((1 - $r['optimal_cost'] / $r['initial_cost']) * 100, 2) : 0,
                ];
            }
        }

        usort($comparison, fn($a, $b) => $a['optimal_cost'] <=> $b['optimal_cost']);

        $this->json([
            'success'     => true,
            'comparison'  => $comparison,
            'best_method' => $comparison[0]['method'] ?? null,
        ]);
    }
}