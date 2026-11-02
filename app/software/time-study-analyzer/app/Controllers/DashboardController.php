<?php
namespace App\Software\TimeStudyAnalyzer\Controllers;

use App\Software\TimeStudyAnalyzer\Models\StudyRepository;

final class DashboardController extends StudyControllerBase
{
    public function index(): void
    {
        $this->renderPage('dashboard/index', [
            'pageTitle' => 'داشبورد Time Study Analyzer',
            'stats' => (new StudyRepository())->dashboard($this->userId()),
        ]);
    }
}
