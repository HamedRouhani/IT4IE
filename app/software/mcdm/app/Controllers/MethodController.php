<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Method;
use App\Software\Mcdm\Models\KnowledgeArea;

class MethodController extends Controller
{
    public function index()
    {
        $methodModel = new Method();
        $kaModel = new KnowledgeArea();

        $kaId = isset($_GET['ka_id']) ? (int)$_GET['ka_id'] : null;
        $category = $_GET['category'] ?? null;

        $methods = $methodModel->getWithKnowledgeArea($kaId, $category);
        $areas = $kaModel->getWithMethodCount();

        $this->view('method/index', [
            'pageTitle'   => 'روش‌های تصمیم‌گیری',
            'currentPage' => 'method',
            'methods'     => $methods,
            'areas'       => $areas,
        ]);
    }

    public function show($id)
    {
        $methodModel = new Method();
        $method = $methodModel->getWithDetails((int)$id);

        if (!$method) {
            $this->flashError('روش یافت نشد.');
            $this->redirect('controller=method');
        }

        $steps = $methodModel->getSteps((int)$id);

        $this->view('method/view', [
            'pageTitle'   => $method['name'],
            'currentPage' => 'method',
            'method'      => $method,
            'steps'       => $steps,
        ]);
    }
}