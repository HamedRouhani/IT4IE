<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Project;
use App\Software\Mcdm\Helpers\AhpCalculator;
use App\Software\Mcdm\Helpers\TopsisCalculator;
use App\Software\Mcdm\Helpers\SawCalculator;
use App\Software\Mcdm\Helpers\VikorCalculator;

class CalculatorController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->json(['success' => false, 'error' => 'Action معتبر نیست.'], 400);
    }

    /**
     * محاسبه وزن معیارها با AHP (مقایسات زوجی) - AJAX
     */
    public function ahpPairwise($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'فقط POST مجاز است.'], 405);
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true) ?: $_POST;

        $projectId = (int)$id;
        $criteria = $this->model->getCriteria($projectId);
        $n = count($criteria);

        if ($n < 2) {
            $this->json(['success' => false, 'error' => 'حداقل دو معیار لازم است.']);
        }

        $matrix = $payload['matrix'] ?? [];
        if (!is_array($matrix) || count($matrix) !== $n) {
            $this->json(['success' => false, 'error' => 'ماتریس معتبر نیست.']);
        }

        $result = AhpCalculator::analyze($matrix);

        if ($result['status'] === 'success') {
            foreach ($criteria as $i => $criterion) {
                if (isset($result['weights'][$i])) {
                    $this->model->updateCriterionWeight((int)$criterion['id'], (float)$result['weights'][$i]);
                }
            }

            $this->model->update($projectId, [
                'consistency_ratio' => $result['consistency_metrics']['CR'],
                'phase'             => 'analysis',
            ]);

            $this->logActivity('ahp_calculate', 'project', $projectId);
        }

        $result['success'] = ($result['status'] === 'success');
        $result['criteria'] = array_map(fn($c) => $c['name'], $criteria);

        $this->json($result);
    }

    /**
     * اجرای روش رتبه‌بندی (SAW / TOPSIS / VIKOR) - AJAX یا redirect
     */
    public function run($id)
    {
        $this->requireAuth();

        $projectId = (int)$id;
        $project = $this->model->getWithDetails($projectId);

        if (!$project) {
            $this->json(['success' => false, 'error' => 'پروژه یافت نشد.'], 404);
        }

        $criteria = $this->model->getCriteria($projectId);
        $alternatives = $this->model->getAlternatives($projectId);
        $evaluations = $this->model->getEvaluations($projectId);

        if (empty($criteria) || empty($alternatives)) {
            $this->json(['success' => false, 'error' => 'ابتدا معیارها و گزینه‌ها را تعریف کنید.']);
        }

        $weights = array_map(fn($c) => (float)($c['weight'] ?? 0), $criteria);
        $types = array_map(fn($c) => $c['type'] ?? 'benefit', $criteria);
        $matrix = $this->buildDecisionMatrix($criteria, $alternatives, $evaluations);

        $methodCode = strtoupper($project['method_code'] ?? 'SAW');

        $result = match ($methodCode) {
            'TOPSIS'        => TopsisCalculator::calculate($matrix, $weights, $types),
            'VIKOR'         => VikorCalculator::calculate($matrix, $weights, $types),
            'AHP', 'SAW'    => SawCalculator::calculate($matrix, $weights, $types),
            default         => ['status' => 'error', 'message' => 'روش پشتیبانی‌نشده: ' . $methodCode],
        };

        if (($result['status'] ?? 'error') === 'success' && isset($result['ranking'])) {
            $altIds = array_column($alternatives, 'id');
            $dbResults = [];
            foreach ($result['ranking'] as $r) {
                $dbResults[] = [
                    'alternative_id' => $altIds[$r['alternative_index']],
                    'score'          => $r['score'],
                    'rank'           => $r['rank'],
                ];
            }
            $this->model->saveResults($projectId, $dbResults);
            $this->model->update($projectId, ['phase' => 'decision']);
            $this->logActivity('run_' . strtolower($methodCode), 'project', $projectId);

            // attach names
            $altNames = array_column($alternatives, 'name', 'id');
            foreach ($dbResults as &$r) {
                $r['alternative_name'] = $altNames[$r['alternative_id']] ?? '';
            }
            $result['ranking_detail'] = $dbResults;
        }

        $result['success'] = (($result['status'] ?? 'error') === 'success');
        $this->json($result);
    }

    private function buildDecisionMatrix(array $criteria, array $alternatives, array $evaluations): array
    {
        $n = count($alternatives);
        $m = count($criteria);
        $matrix = array_fill(0, $n, array_fill(0, $m, 0.0));

        $altIndex = array_flip(array_column($alternatives, 'id'));
        $critIndex = array_flip(array_column($criteria, 'id'));

        foreach ($evaluations as $e) {
            $row = $altIndex[$e['alternative_id']] ?? null;
            $col = $critIndex[$e['criterion_id']] ?? null;
            if ($row !== null && $col !== null) {
                $matrix[$row][$col] = (float)$e['value'];
            }
        }

        return $matrix;
    }
}