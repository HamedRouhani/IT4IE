<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\MonteCarloProject;
use App\Software\Or\Helpers\MonteCarloEngine as MC;

class MonteCarloController extends Controller
{
    private $mcModel;

    public function __construct()
    {
        parent::__construct();
        $this->mcModel = new MonteCarloProject();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('monte_carlo/index', [
            'pageTitle'   => 'شبیه‌سازی مونت‌کارلو',
            'currentPage' => 'monte_carlo',
            'distributions' => MC::listDistributions(),
            'projects'    => $this->mcModel->getByUser($this->currentUserId, 20),
        ]);
    }

    public function simulate(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $variables = $payload['variables'] ?? [];
            $funcExpr = trim($payload['function'] ?? '');
            $iterations = (int)($payload['iterations'] ?? 10000);
            $seed = isset($payload['seed']) && $payload['seed'] !== '' ? (int)$payload['seed'] : null;
            $save = !empty($payload['save']);
            $name = trim($payload['name'] ?? '');

            if (empty($funcExpr)) throw new \Exception('عبارت تابع هدف الزامی است.');

            $result = MC::simulate($variables, $funcExpr, $iterations, $seed);

            $projectId = null;
            if ($save) {
                $projectId = $this->mcModel->createMonteCarlo([
                    'user_id'        => $this->currentUserId,
                    'name'           => $name ?: ("مونت‌کارلو - " . date('Y-m-d H:i')),
                    'description'    => $payload['description'] ?? null,
                    'variables_json' => json_encode($variables, JSON_UNESCAPED_UNICODE),
                    'function_expr'  => $funcExpr,
                    'iterations'     => $iterations,
                    'seed'           => $seed,
                    'result_json'    => json_encode($result, JSON_UNESCAPED_UNICODE),
                ]);
                $this->logActivity('create_monte_carlo', 'monte_carlo', $projectId);
            }

            $this->json([
                'success'    => true,
                'result'     => $result,
                'project_id' => $projectId,
            ]);

        } catch (\Throwable $e) {
            error_log('OR MonteCarlo Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->mcModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه مونت‌کارلو یافت نشد.');
            $this->redirect('controller=monte_carlo');
            return;
        }

        $result = json_decode($project['result_json'] ?? '{}', true) ?: [];
        $variables = json_decode($project['variables_json'] ?? '[]', true) ?: [];

        $this->view('monte_carlo/show', [
            'pageTitle'   => 'گزارش مونت‌کارلو: ' . $project['name'],
            'currentPage' => 'monte_carlo',
            'project'     => $project,
            'result'      => $result,
            'variables'   => $variables,
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $row = $this->mcModel->findOwned($id, $this->currentUserId);
        if (!$row) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=monte_carlo');
            return;
        }
        $this->mcModel->deleteOwned($id, $this->currentUserId);
        $this->logActivity('delete_monte_carlo', 'monte_carlo', $id);
        $this->flashSuccess('پروژه حذف شد.');
        $this->redirect('controller=monte_carlo');
    }
}