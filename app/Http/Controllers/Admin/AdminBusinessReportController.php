<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessOrder;
use App\Models\Payment;
use App\Services\Business\AdminBusinessSummaryService;
use Illuminate\View\View;

class AdminBusinessReportController extends Controller
{
    public function __construct(
        private readonly AdminBusinessSummaryService $summaryService,
    ) {
    }

    public function index(): View
    {
        return view('admin.business.reports', [
            'report' => $this->summaryService->report(),
            'orderStatusLabels' => BusinessOrder::STATUS_LABELS,
            'paymentStatusLabels' => Payment::STATUS_LABELS,
            'seo' => [
                'title' => 'Business reports',
                'description' => 'Business health report for orders, revenue, stock risk, and customer readiness.',
            ],
        ]);
    }
}
