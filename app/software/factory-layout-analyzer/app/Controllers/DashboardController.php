<?php

declare(strict_types=1);

namespace App\Software\FactoryLayoutAnalyzer\Controllers;

use App\Core\Controller;
use App\Software\FactoryLayoutAnalyzer\Content\Curriculum;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $lessons = Curriculum::lessons();
        $this->renderSoftware('dashboard/index', [
            'pageTitle' => 'طراحی چیدمان کارخانه',
            'title' => 'طراحی چیدمان کارخانه - IT4IE',
            'softwareName' => 'Factory Layout Analyzer',
            'lessonCount' => count($lessons),
            'firstLesson' => $lessons[0] ?? null,
        ], 'factory-layout-analyzer');
    }
}
