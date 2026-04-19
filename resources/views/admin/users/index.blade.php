@extends('layouts.site')

@section('content')
    <section class="page-section">
        <div class="site-container">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 class="section-heading">Admin потребители</h1>
                    <p class="section-copy">Управление на потребителски акаунти, контактни данни и статус на потвърждение.</p>
                </div>
                <a href="{{ route('admin.users.create') }}" class="btn-primary">Нов потребител</a>
            </div>

            <div class="mt-8">
                @include('partials.admin-nav')
            </div>

            <div class="card-soft overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Име</th>
                            <th class="px-4 py-3">Имейл</th>
                            <th class="px-4 py-3">Телефон</th>
                            <th class="px-4 py-3">Роля</th>
                            <th class="px-4 py-3">Потвърден</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr class="border-t">
                                <td class="px-4 py-3">#{{ $user->id }}</td>
                                <td class="px-4 py-3">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ $user->email }}</td>
                                <td class="px-4 py-3">{{ $user->phone ?: '—' }}</td>
                                <td class="px-4 py-3">{{ $user->role }}</td>
                                <td class="px-4 py-3">{{ $user->hasVerifiedEmail() ? 'Да' : 'Не' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="font-medium text-primary">Редакция</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8">
                {{ $users->links() }}
            </div>
        </div>
    </section>
@endsection
