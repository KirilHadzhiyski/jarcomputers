@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">Входящи заявки и съобщения</h1>
    <p class="section-copy">Заявките от сайта и разговорите от свързаните канали на едно място.</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <div class="card-soft"><h2 class="text-xl font-semibold">Заявки за ремонт</h2><div class="admin-table-wrap mt-5"><table class="min-w-full text-left text-sm"><thead><tr><th class="p-3">Клиент</th><th class="p-3">Устройство / проблем</th><th class="p-3">Получена</th></tr></thead><tbody>
    @forelse ($requests as $request)<tr class="border-t border-border"><td class="p-3">{{ $request->name }}<p class="text-muted-foreground">{{ $request->phone }} {{ $request->email }}</p></td><td class="p-3">{{ $request->model }}<p class="mt-1 whitespace-pre-wrap text-muted-foreground">{{ $request->issue }}</p></td><td class="p-3 whitespace-nowrap">{{ $request->created_at->format('d.m.Y H:i') }}</td></tr>@empty<tr><td colspan="3" class="p-6 text-muted-foreground">Все още няма заявки от сайта.</td></tr>@endforelse
    </tbody></table></div><div class="mt-4">{{ $requests->withQueryString()->links() }}</div></div>
    <div class="card-soft mt-8"><h2 class="text-xl font-semibold">Разговори</h2><div class="mt-5 space-y-3">
    @forelse ($conversations as $conversation)<a class="block rounded-xl border border-border p-4 hover:bg-secondary" href="{{ route('admin.inbox.show', $conversation) }}"><div class="flex flex-wrap justify-between gap-3"><strong>{{ $conversation->customer_name ?: $conversation->customer_email ?: $conversation->customer_phone ?: 'Нов контакт' }}</strong><span class="text-xs text-muted-foreground">{{ $conversation->last_message_at?->format('d.m.Y H:i') }}</span></div><p class="mt-2 text-sm text-muted-foreground">{{ $conversation->channel }} · {{ $conversation->messages_count }} съобщения · {{ $conversation->customer_email ?: $conversation->customer_phone }}</p></a>@empty<p class="text-sm text-muted-foreground">Няма разговори. Новите съобщения от активирани канали ще се появяват тук.</p>@endforelse
    </div><div class="mt-4">{{ $conversations->withQueryString()->links() }}</div></div>
</div></section>
@endsection
