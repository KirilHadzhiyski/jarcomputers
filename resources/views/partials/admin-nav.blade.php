<div class="mb-8 flex flex-wrap gap-3">
    <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Overview</a>
    <a href="{{ route('admin.business.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.dashboard') ? 'nav-link-active' : '' }}">Business</a>
    <a href="{{ route('admin.business.index', 'orders') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'orders' ? 'nav-link-active' : '' }}">Orders</a>
    <a href="{{ route('admin.business.index', 'customers') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'customers' ? 'nav-link-active' : '' }}">Customers</a>
    <a href="{{ route('admin.business.index', 'inventory') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'inventory' ? 'nav-link-active' : '' }}">Inventory</a>
    <a href="{{ route('admin.business.index', 'suppliers') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'suppliers' ? 'nav-link-active' : '' }}">Suppliers</a>
    <a href="{{ route('admin.business.index', 'payments') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'payments' ? 'nav-link-active' : '' }}">Payments</a>
    <a href="{{ route('admin.business.index', 'services') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'services' ? 'nav-link-active' : '' }}">Services</a>
    <a href="{{ route('admin.business.index', 'communications') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'communications' ? 'nav-link-active' : '' }}">Messages</a>
    <a href="{{ route('admin.business.index', 'reviews') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'reviews' ? 'nav-link-active' : '' }}">Reviews</a>
    <a href="{{ route('admin.business.index', 'marketing') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'marketing' ? 'nav-link-active' : '' }}">SEO Pages</a>
    <a href="{{ route('admin.business.reports') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.reports') ? 'nav-link-active' : '' }}">Reports</a>
    <a href="{{ route('admin.pricing.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.dashboard') ? 'nav-link-active' : '' }}">Pricing</a>
    <a href="{{ route('admin.pricing.configurations.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.configurations.*') ? 'nav-link-active' : '' }}">Configurations</a>
    <a href="{{ route('admin.pricing.markets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.markets.*') ? 'nav-link-active' : '' }}">Markets & VAT</a>
    <a href="{{ route('admin.pricing.sources.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.sources.*') ? 'nav-link-active' : '' }}">Sources</a>
    <a href="{{ route('admin.pricing.benchmarks.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.benchmarks.*') ? 'nav-link-active' : '' }}">Benchmarks</a>
    <a href="{{ route('admin.pricing.analysis.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.analysis.*') ? 'nav-link-active' : '' }}">Analysis</a>
    <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.tickets.*') ? 'nav-link-active' : '' }}">Tickets</a>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Users</a>
</div>
