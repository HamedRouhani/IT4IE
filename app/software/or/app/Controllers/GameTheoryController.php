<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\GameTheoryProject;
use App\Software\Or\Helpers\GameTheoryEngine;

class GameTheoryController extends Controller
{
    private $gameModel;

    public function __construct()
    {
        parent::__construct();
        $this->gameModel = new GameTheoryProject();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('game_theory/index', [
            'pageTitle'   => 'نظریه بازی (Game Theory)',
            'currentPage' => 'game_theory',
            'projects'    => $this->gameModel->getByUser($this->currentUserId, 20),
        ]);
    }

    public function solve(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $gameType = $payload['game_type'] ?? 'zero_sum';
            $p1Strats = $payload['p1_strats'] ?? [];
            $p2Strats = $payload['p2_strats'] ?? [];
            $matrixA = $payload['matrix_a'] ?? [];
            $matrixB = $payload['matrix_b'] ?? [];
            $save = !empty($payload['save']);
            $name = trim($payload['name'] ?? '');

            if (count($p1Strats) < 2 || count($p2Strats) < 2) {
                throw new \Exception('هر بازیکن باید حداقل ۲ استراتژی داشته باشد.');
            }

            $result = GameTheoryEngine::solve($gameType, $p1Strats, $p2Strats, $matrixA, $matrixB);

            if ($result['status'] === 'error') {
                $this->json(['success' => false, 'error' => $result['message']]);
                return;
            }

            $projectId = null;
            if ($save) {
                $projectId = $this->gameModel->createGame([
                    'user_id'             => $this->currentUserId,
                    'name'                => $name ?: ("بازی - " . date('Y-m-d H:i')),
                    'description'         => $payload['description'] ?? null,
                    'player1_strategies'  => json_encode($p1Strats, JSON_UNESCAPED_UNICODE),
                    'player2_strategies'  => json_encode($p2Strats, JSON_UNESCAPED_UNICODE),
                    'payoff_matrix'       => json_encode(['A' => $matrixA, 'B' => $matrixB], JSON_UNESCAPED_UNICODE),
                    'result_json'         => json_encode($result, JSON_UNESCAPED_UNICODE),
                    'status'              => 'solved',
                ]);
                $this->logActivity('create_game_theory', 'game_theory', $projectId);
            }

            $this->json(['success' => true, 'result' => $result, 'project_id' => $projectId]);

        } catch (\Throwable $e) {
            error_log('OR GameTheory Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->gameModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=game_theory');
            return;
        }

        $matrices = json_decode($project['payoff_matrix'] ?? '{}', true) ?: [];
        $this->view('game_theory/show', [
            'pageTitle'   => 'گزارش نظریه بازی: ' . $project['name'],
            'currentPage' => 'game_theory',
            'project'     => $project,
            'result'      => json_decode($project['result_json'] ?? '{}', true) ?: [],
            'p1_strats'   => json_decode($project['player1_strategies'] ?? '[]', true) ?: [],
            'p2_strats'   => json_decode($project['player2_strategies'] ?? '[]', true) ?: [],
            'matrix_a'    => $matrices['A'] ?? [],
            'matrix_b'    => $matrices['B'] ?? [],
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($this->gameModel->deleteOwned($id, $this->currentUserId)) {
            $this->logActivity('delete_game_theory', 'game_theory', $id);
            $this->flashSuccess('پروژه حذف شد.');
        } else {
            $this->flashError('پروژه یافت نشد.');
        }
        $this->redirect('controller=game_theory');
    }
}