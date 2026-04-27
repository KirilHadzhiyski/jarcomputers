<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Business\AdminBusinessSummaryService;
use App\Support\AdminBusinessResources;
use Illuminate\View\View;

class AdminBusinessDashboardController extends Controller
{
    public function __construct(
        private readonly AdminBusinessSummaryService $summaryService,
    ) {
    }

    public function index(): View
    {
        return view('admin.business.dashboard', [
            'resources' => AdminBusinessResources::menu(),
            'summary' => $this->summaryService->summary(),
            'seo' => [
                'title' => 'Business operations',
                'description' => 'Operational backoffice for orders, customers, inventory, services, messaging, reviews, and reports.',
            ],
        ]);
    }
}
