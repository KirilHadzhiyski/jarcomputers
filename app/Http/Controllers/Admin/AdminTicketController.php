<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TicketCustomerUpdateMail;
use App\Models\Ticket;
use App\Models\TicketUpdate;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminTicketController extends Controller
{
    public function index(): View
    {
        return view('admin.tickets.index', [
            'tickets' => Ticket::query()->with('user')->latest()->paginate(15),
            'statusLabels' => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'seo' => [
                'title' => 'Admin билети',
                'description' => 'Управление на сервизни поръчки и комуникация с клиенти.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.tickets.create', [
            'users' => User::query()->orderBy('name')->get(),
            'statusLabels' => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
            'seo' => [
                'title' => 'Нова поръчка',
                'description' => 'Административно създаване на сервизна поръчка.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['resolved_at'] = in_array($validated['status'], ['resolved', 'closed'], true) ? now() : null;

        $ticket = Ticket::query()->create($validated);
        $this->storeTicketUpdate(
            request: $request,
            ticket: $ticket,
            oldStatus: null,
            message: $request->string('customer_message')->trim()->value() ?: 'Поръчката е приета от нашия екип и вече е в системата.',
        );

        return redirect()
            ->route('admin.tickets.edit', $ticket)
            ->with('status', 'Поръчката беше създадена.');
    }

    public function edit(Ticket $ticket): View
    {
        return view('admin.tickets.edit', [
            'ticket' => $ticket->load([
                'updates' => fn ($query) => $query->with('author')->latest(),
            ]),
            'users' => User::query()->orderBy('name')->get(),
            'statusLabels' => Ticket::STATUS_LABELS,
            'priorityLabels' => Ticket::PRIORITY_LABELS,
            'categoryLabels' => Ticket::CATEGORY_LABELS,
            'seo' => [
                'title' => "Редакция на поръчка #{$ticket->id}",
                'description' => 'Редакция на сервизна поръчка и комуникация с клиента.',
            ],
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $this->validated($request);
        $oldStatus = $ticket->status;
        $validated['resolved_at'] = in_array($validated['status'], ['resolved', 'closed'], true) ? ($ticket->resolved_at ?? now()) : null;

        $ticket->update($validated);

        $message = $request->string('customer_message')->trim()->value();
        $statusChanged = $oldStatus !== $ticket->status;

        if ($statusChanged || filled($message)) {
            $message = $message ?: sprintf(
                'Статусът на поръчката беше обновен на "%s".',
                $ticket->statusLabel(),
            );

            $this->storeTicketUpdate(
                request: $request,
                ticket: $ticket,
                oldStatus: $oldStatus,
                message: $message,
            );
        }

        return redirect()
            ->route('admin.tickets.edit', $ticket)
            ->with('status', 'Промените по поръчката са записани.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $ticket->delete();

        return redirect()
            ->route('admin.tickets.index')
            ->with('status', 'Поръчката беше изтрита.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:160'],
            'device_model' => ['nullable', 'string', 'max:100'],
            'category' => ['required', 'in:repair,warranty,question,other'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'status' => ['required', 'in:open,in_progress,waiting_customer,ready_for_pickup,resolved,closed'],
            'description' => ['required', 'string', 'max:4000'],
            'admin_notes' => ['nullable', 'string', 'max:4000'],
        ]);
    }

    private function storeTicketUpdate(Request $request, Ticket $ticket, ?string $oldStatus, string $message): TicketUpdate
    {
        $update = $ticket->updates()->create([
            'author_id' => $request->user()->id,
            'old_status' => $oldStatus,
            'new_status' => $ticket->status,
            'message' => $message,
            'is_internal' => false,
        ]);

        if ($request->boolean('notify_customer') || in_array($ticket->status, ['ready_for_pickup', 'resolved', 'closed'], true)) {
            Mail::to($ticket->user->email)->send(new TicketCustomerUpdateMail($ticket->fresh('user'), $update));
            $update->forceFill([
                'emailed_at' => now(),
            ])->save();
        }

        return $update;
    }
}
