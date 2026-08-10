<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\KnowledgeArea;
use App\Software\Babok\Models\Task;

/**
 * کنترلر مدیریت حوزه‌های دانشی BABOK
 * جدول: babok_knowledge_areas (۶ حوزه: KA1 تا KA6)
 */
class KnowledgeAreaController extends Controller
{
    private $knowledgeAreaModel;
    private $taskModel;

    public function __construct()
    {
        $this->knowledgeAreaModel = new KnowledgeArea();
        $this->taskModel = new Task();
    }

    /**
     * لیست همه حوزه‌های دانشی
     */
    public function index()
    {
        $knowledgeAreas = $this->knowledgeAreaModel->getAllWithCount();

        $this->view('knowledge-areas/index', [
            'title' => 'حوزه‌های دانشی BABOK - BABOK Analyzer',
            'activePage' => 'knowledge_areas',
            'knowledgeAreas' => $knowledgeAreas
        ]);
    }

    /**
     * مشاهده جزئیات یک حوزه دانشی
     */
    public function show($id)
    {
        $knowledgeArea = $this->knowledgeAreaModel->find($id);

        if (!$knowledgeArea) {
            $this->flashError('حوزه مورد نظر یافت نشد.');
            $this->redirect('knowledge_areas');
            return;
        }

        $tasks = $this->knowledgeAreaModel->getTasksWithTechniques($id);

        $this->view('knowledge-areas/view', [
            'title' => $knowledgeArea['name'] . ' - BABOK Analyzer',
            'activePage' => 'knowledge_areas',
            'knowledgeArea' => $knowledgeArea,
            'tasks' => $tasks
        ]);
    }
}