<div class="mb-8 flex flex-wrap gap-3">
    <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Обзор</a>
    <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.tickets.*') ? 'nav-link-active' : '' }}">Билети</a>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Потребители</a>
</div>
