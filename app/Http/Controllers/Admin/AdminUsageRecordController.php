<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsageRecord;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUsageRecordController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->integer('user_id');

        return view('admin.usage-records.index', [
            'records' => UsageRecord::query()->with(['user', 'workflow'])->when($userId, fn ($query) => $query->where('user_id', $userId))->latest('occurred_at')->paginate(25)->withQueryString(),
            'users' => User::query()->where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']),
            'workflows' => Workflow::query()->orderBy('name')->get(['id', 'name', 'client_user_id']),
            'serviceLabels' => UsageRecord::SERVICE_LABELS,
            'selectedUserId' => $userId,
            'monthCosts' => UsageRecord::query()
                ->where('occurred_at', '>=', now()->startOfMonth())
                ->selectRaw('currency_code, sum(cost) as total_cost')
                ->groupBy('currency_code')
                ->pluck('total_cost', 'currency_code'),
            'seo' => ['title' => 'AI и API потребление'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        UsageRecord::query()->create($request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'workflow_id' => ['nullable', 'exists:workflows,id'],
            'service_type' => ['required', Rule::in(array_keys(UsageRecord::SERVICE_LABELS))],
            'provider' => ['required', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:120'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:40'],
            'cost' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['required', 'regex:/^[A-Z]{3}$/'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
        ]));

        return back()->with('status', 'Записът за потребление е добавен.');
    }
}
