@extends('layouts.admin')
@section('content')
<section class="page-section"><div class="site-container">
    <h1 class="section-heading">{{ $conversation->customer_name ?: 'Разговор с клиент' }}</h1>
    <p class="section-copy">{{ $conversation->channel }} · {{ $conversation->customer_email }} {{ $conversation->customer_phone }}</p>
    <div class="mt-8">@include('partials.admin-nav')</div>
    <div class="mb-6 flex flex-wrap gap-3"><a class="btn-secondary-dark" href="{{ route('admin.inbox.index') }}">Всички разговори</a>@if ($order)<a class="btn-primary" href="{{ route('admin.business.edit', ['orders',$order->id]) }}">Отвори поръчка {{ $order->order_number }}</a>@endif</div>
    <div class="space-y-4">@forelse ($messages as $message)<article class="card-soft"><div class="flex flex-wrap justify-between gap-3 text-sm text-muted-foreground"><span>{{ $message->sender_name ?: $message->sender_handle ?: 'Клиент' }}</span><time>{{ $message->received_at?->format('d.m.Y H:i') }}</time></div><p class="mt-4 whitespace-pre-wrap break-words text-sm leading-7">{{ $message->content ?: 'Съобщение без текст' }}</p></article>@empty<p>Няма съобщения.</p>@endforelse</div><div class="mt-5">{{ $messages->links() }}</div>
</div></section>
@endsection
