<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        return view('admin.audit-log.index', [
            'logs' => AuditLog::query()
                ->with('user')
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('route_name', 'like', "%{$search}%")
                        ->orWhere('path', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%");
                }))
                ->latest('created_at')
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
            'seo' => [
                'title' => 'Журнал на действията',
                'description' => 'Проследим журнал на промените, извършени през административния панел.',
            ],
        ]);
    }
}
