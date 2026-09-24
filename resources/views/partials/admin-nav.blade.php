<div class="mb-8 flex flex-wrap gap-3">
    <a href="{{ route('admin.skroutz.index') }}" class="btn-secondary-dark">Skroutz</a>
    <a href="{{ route('admin.integrations.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.integrations.index') ? 'nav-link-active' : '' }}">Интеграции</a>
    <a href="{{ route('admin.inbox.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.inbox.*') ? 'nav-link-active' : '' }}">Входящи заявки</a>
    <a href="{{ route('admin.integrations.catalog') }}" class="btn-secondary-dark {{ request()->routeIs('admin.integrations.catalog') ? 'nav-link-active' : '' }}">JAR каталог</a>
    <a href="{{ route('admin.integrations.documents') }}" class="btn-secondary-dark {{ request()->routeIs('admin.integrations.documents') ? 'nav-link-active' : '' }}">JAR документи</a>
    <a href="{{ route('admin.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Начало</a>
    <a href="{{ route('admin.business.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.dashboard') ? 'nav-link-active' : '' }}">Бизнес</a>
    <a href="{{ route('admin.business.index', 'orders') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'orders' ? 'nav-link-active' : '' }}">Поръчки</a>
    <a href="{{ route('admin.business.index', 'customers') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'customers' ? 'nav-link-active' : '' }}">Клиенти</a>
    <a href="{{ route('admin.business.index', 'inventory') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'inventory' ? 'nav-link-active' : '' }}">Склад</a>
    <a href="{{ route('admin.business.index', 'suppliers') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'suppliers' ? 'nav-link-active' : '' }}">Доставчици</a>
    <a href="{{ route('admin.business.index', 'payments') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'payments' ? 'nav-link-active' : '' }}">Плащания</a>
    <a href="{{ route('admin.business.index', 'services') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'services' ? 'nav-link-active' : '' }}">Услуги</a>
    <a href="{{ route('admin.business.index', 'communications') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'communications' ? 'nav-link-active' : '' }}">Шаблони</a>
    <a href="{{ route('admin.business.index', 'reviews') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'reviews' ? 'nav-link-active' : '' }}">Отзиви</a>
    <a href="{{ route('admin.business.index', 'marketing') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.*') && request()->route('resource') === 'marketing' ? 'nav-link-active' : '' }}">SEO страници</a>
    <a href="{{ route('admin.business.reports') }}" class="btn-secondary-dark {{ request()->routeIs('admin.business.reports') ? 'nav-link-active' : '' }}">Отчети</a>
    <a href="{{ route('admin.pricing.dashboard') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.dashboard') ? 'nav-link-active' : '' }}">Ценови анализ</a>
    <a href="{{ route('admin.pricing.configurations.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.configurations.*') ? 'nav-link-active' : '' }}">Конфигурации</a>
    <a href="{{ route('admin.pricing.markets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.markets.*') ? 'nav-link-active' : '' }}">Пазари и ДДС</a>
    <a href="{{ route('admin.pricing.sources.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.sources.*') ? 'nav-link-active' : '' }}">Източници</a>
    <a href="{{ route('admin.pricing.benchmarks.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.benchmarks.*') ? 'nav-link-active' : '' }}">Оферти</a>
    <a href="{{ route('admin.pricing.analysis.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.pricing.analysis.*') ? 'nav-link-active' : '' }}">Резултати</a>
    <a href="{{ route('admin.tickets.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.tickets.*') ? 'nav-link-active' : '' }}">Билети</a>
    <a href="{{ route('admin.users.index') }}" class="btn-secondary-dark {{ request()->routeIs('admin.users.*') ? 'nav-link-active' : '' }}">Потребители</a>
</div>
