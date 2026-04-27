<div class="mb-8 flex flex-wrap gap-3">
    <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Overview</a>
    <a href="{{ route('admin.pricing.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.dashboard') ? 'nav-link-active' : '' }}">Pricing</a>
    <a href="{{ route('admin.pricing.configurations.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.configurations.*') ? 'nav-link-active' : '' }}">Configurations</a>
    <a href="{{ route('admin.pricing.markets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.markets.*') ? 'nav-link-active' : '' }}">Markets & VAT</a>
    <a href="{{ route('admin.pricing.sources.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.sources.*') ? 'nav-link-active' : '' }}">Sources</a>
    <a href="{{ route('admin.pricing.benchmarks.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.benchmarks.*') ? 'nav-link-active' : '' }}">Benchmarks</a>
    <a href="{{ route('admin.pricing.analysis.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.analysis.*') ? 'nav-link-active' : '' }}">Analysis</a>
    <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.tickets.*') ? 'nav-link-active' : '' }}">Tickets</a>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Users</a>
</div>
