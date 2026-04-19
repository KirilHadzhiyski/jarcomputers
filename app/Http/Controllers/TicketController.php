<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(Request $request): View
    {
        return view('tickets.index', [
            'tickets' => $request->user()->tickets()->latest()->paginate(10),
            'statusLabels' => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
            'seo' => [
                'title' => 'Моите поръчки',
                'description' => 'Преглед на всички ваши поръчки и техния статус.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('tickets.create', [
            'categoryLabels' => Ticket::CATEGORY_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'seo' => [
                'title' => 'Нова поръчка',
                'description' => 'Подайте нова сервизна поръчка.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'in:repair,warranty,question,other'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'description' => ['required', 'string', 'max:4000'],
        ]);

        $validated['status'] = 'open';
        $ticket = $request->user()->tickets()->create($validated);
        $ticket->updates()->create([
            'author_id' => $request->user()->id,
            'new_status' => $ticket->status,
            'message' => 'Поръчката е подадена успешно и очаква обработка от нашия екип.',
            'is_internal' => false,
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Поръчката беше създадена успешно.');
    }

    public function show(Request $request, Ticket $ticket): View
    {
        abort_unless($ticket->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('tickets.show', [
            'ticket' => $ticket->load([
                'user',
                'updates' => fn ($query) => $query->where('is_internal', false)->with('author')->latest(),
            ]),
            'statusLabels' => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
            'seo' => [
                'title' => "Поръчка #{$ticket->id}",
                'description' => 'Проследяване на сервизна поръчка.',
            ],
        ]);
    }
}
