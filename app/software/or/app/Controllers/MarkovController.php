<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\MarkovProject;
use App\Software\Or\Helpers\MarkovEngine;

class MarkovController extends Controller
{
    private $markovModel;

    public function __construct()
    {
        parent::__construct();
        $this->markovModel = new MarkovProject();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('markov/index', [
            'pageTitle'   => 'زنجیره مارکوف',
            'currentPage' => 'markov',
            'projects'    => $this->markovModel->getByUser($this->currentUserId, 20),
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
            $states = $payload['states'] ?? [];
            $matrix = $payload['matrix'] ?? [];
            $initial = $payload['initial'] ?? [];
            $steps = (int)($payload['steps'] ?? 5);
            $save = !empty($payload['save']);
            $name = trim($payload['name'] ?? '');

            if (count($states) < 2) throw new \Exception('حداقل ۲ حالت نیاز است.');
            if (count($initial) !== count($states)) throw new \Exception('تعداد درایه‌های حالت اولیه با تعداد حالت‌ها مطابقت ندارد.');

            $result = MarkovEngine::solve($states, $matrix, $initial, $steps);

            if ($result['status'] === 'error') {
                $this->json(['success' => false, 'error' => implode(' | ', $result['errors'])]);
                return;
            }

            $projectId = null;
            if ($save) {
                $projectId = $this->markovModel->createMarkov([
                    'user_id'                => $this->currentUserId,
                    'name'                   => $name ?: ("مارکوف - " . date('Y-m-d H:i')),
                    'description'            => $payload['description'] ?? null,
                    'states_json'            => json_encode($states, JSON_UNESCAPED_UNICODE),
                    'transition_matrix_json' => json_encode($matrix, JSON_UNESCAPED_UNICODE),
                    'initial_state_json'     => json_encode($initial, JSON_UNESCAPED_UNICODE),
                    'steps'                  => $steps,
                    'result_json'            => json_encode($result, JSON_UNESCAPED_UNICODE),
                ]);
                $this->logActivity('create_markov', 'markov', $projectId);
            }

            $this->json(['success' => true, 'result' => $result, 'project_id' => $projectId]);

        } catch (\Throwable $e) {
            error_log('OR Markov Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->markovModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=markov');
            return;
        }

        $this->view('markov/show', [
            'pageTitle'   => 'گزارش مارکوف: ' . $project['name'],
            'currentPage' => 'markov',
            'project'     => $project,
            'result'      => json_decode($project['result_json'] ?? '{}', true) ?: [],
            'states'      => json_decode($project['states_json'] ?? '[]', true) ?: [],
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($this->markovModel->deleteOwned($id, $this->currentUserId)) {
            $this->logActivity('delete_markov', 'markov', $id);
            $this->flashSuccess('پروژه حذف شد.');
        } else {
            $this->flashError('پروژه یافت نشد.');
        }
        $this->redirect('controller=markov');
    }
}