<?php
namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\KnowledgeArea;

class SearchController extends Controller
{
    private $taskModel;
    private $techniqueModel;
    private $kaModel;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->techniqueModel = new Technique();
        $this->kaModel = new KnowledgeArea();
    }

    public function index()
    {
        $this->view('search/index', [
            'title' => 'جستجوی هوشمند - BABOK Analyzer',
            'activePage' => 'search',
            'query' => '',
            'results' => null
        ]);
    }

    public function search()
    {
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query) || mb_strlen($query) < 2) {
            $this->view('search/index', [
                'title' => 'جستجوی هوشمند - BABOK Analyzer',
                'activePage' => 'search',
                'query' => $query,
                'results' => null,
                'error' => 'لطفاً حداقل ۲ کاراکتر وارد کنید.'
            ]);
            return;
        }

        $tasks = $this->taskModel->semanticSearch($query, 5);
        $techniques = $this->techniqueModel->semanticSearch($query, 5);
        $knowledgeAreas = $this->kaModel->semanticSearch($query, 5);

        foreach ($tasks as &$task) {
            $ka = $this->kaModel->find($task['knowledge_area_id']);
            $task['ka_name'] = $ka['name'] ?? 'نامشخص';
            $task['ka_code'] = $ka['code'] ?? '';
        }
        unset($task);

        $results = [
            'tasks' => $tasks,
            'techniques' => $techniques,
            'knowledge_areas' => $knowledgeAreas,
            'total' => count($tasks) + count($techniques) + count($knowledgeAreas)
        ];

        $this->view('search/index', [
            'title' => "نتایج جستجو: {$query} - BABOK Analyzer",
            'activePage' => 'search',
            'query' => $query,
            'results' => $results
        ]);
    }

    public function ajax()
    {
        header('Content-Type: application/json; charset=utf-8');
        $query = trim($_GET['q'] ?? '');
        
        if (empty($query) || mb_strlen($query) < 2) {
            echo json_encode(['success' => true, 'results' => []], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $tasks = $this->taskModel->semanticSearch($query, 3);
        $techniques = $this->techniqueModel->semanticSearch($query, 3);

        foreach ($tasks as &$task) {
            $ka = $this->kaModel->find($task['knowledge_area_id']);
            $task['ka_name'] = $ka['name'] ?? '';
        }
        unset($task);

        echo json_encode(['success' => true, 'results' => [
            'tasks' => $tasks,
            'techniques' => $techniques
        ]], JSON_UNESCAPED_UNICODE);
        exit;
    }
}