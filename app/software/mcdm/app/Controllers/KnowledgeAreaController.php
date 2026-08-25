<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\KnowledgeArea;
use App\Software\Mcdm\Models\Method;

class KnowledgeAreaController extends Controller
{
    public function index()
    {
        $kaModel = new KnowledgeArea();
        $areas = $kaModel->getWithMethodCount();

        $this->view('knowledgearea/index', [
            'pageTitle'   => 'حوزه‌های دانشی',
            'currentPage' => 'knowledgearea',
            'areas'       => $areas,
        ]);
    }

    public function show($id)
    {
        $kaModel = new KnowledgeArea();
        $methodModel = new Method();

        $area = $kaModel->find((int)$id);
        if (!$area) {
            $this->flashError('حوزه دانشی یافت نشد.');
            $this->redirect('controller=knowledgearea');
        }

        $methods = $methodModel->getWithKnowledgeArea((int)$id);

        $this->view('knowledgearea/view', [
            'pageTitle'   => $area['name_fa'] ?? $area['name'],
            'currentPage' => 'knowledgearea',
            'area'        => $area,
            'methods'     => $methods,
        ]);
    }
}