<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\KnowledgeArea;
use App\Models\Task;

class KnowledgeAreaController extends Controller
{
    private $knowledgeAreaModel;
    private $taskModel;

    public function __construct()
    {
        $this->knowledgeAreaModel = new KnowledgeArea();
        $this->taskModel = new Task();
    }

    // لیست همه حوزه‌های دانشی
    public function index()
    {
        $knowledgeAreas = $this->knowledgeAreaModel->getAll();
        
        // دریافت تعداد وظایف هر حوزه
        foreach ($knowledgeAreas as &$area) {
            $tasks = $this->taskModel->getByKnowledgeArea($area['id']);
            $area['task_count'] = count($tasks);
        }

        $this->view('knowledge-areas/index', [
            'knowledgeAreas' => $knowledgeAreas
        ]);
    }

    // مشاهده جزئیات یک حوزه
    public function show($id)
    {
        $knowledgeArea = $this->knowledgeAreaModel->find($id);
        if (!$knowledgeArea) {
            $_SESSION['flash_error'] = 'حوزه مورد نظر یافت نشد.';
            $this->redirect('/knowledge-areas');
        }

        $tasks = $this->knowledgeAreaModel->getTasksWithTechniques($id);

        $this->view('knowledge-areas/view', [
            'knowledgeArea' => $knowledgeArea,
            'tasks' => $tasks
        ]);
    }
}