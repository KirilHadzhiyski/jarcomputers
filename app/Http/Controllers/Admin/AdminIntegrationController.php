<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalDocument;
use App\Models\IntegrationConnection;
use App\Models\IntegrationRun;
use App\Models\SupplierProduct;
use App\Services\Integrations\IntegrationException;
use App\Services\Integrations\IntegrationManager;
use App\Services\Integrations\IntegrationRegistry;
use App\Services\Integrations\PriceFeedConnector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminIntegrationController extends Controller
{
    public function index()
    {
        return view('admin.integrations.index', [
            'connections' => IntegrationConnection::with(['runs' => fn ($query) => $query->limit(1)])->get()->keyBy('provider'),
            'definitions' => IntegrationRegistry::definitions(),
            'runs' => IntegrationRun::with('connection')->latest('id')->paginate(15),
            'rates' => DB::table('exchange_rates')->orderBy('currency_code')->get(),
            'schedulerHeartbeat' => Cache::get('integrations:scheduler_heartbeat'),
            'workerHeartbeat' => Cache::get('integrations:worker_heartbeat'),
            'seo' => ['title' => 'Интеграции и автоматизация', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function update(Request $request, string $provider)
    {
        $definition = IntegrationRegistry::definition($provider);
        $connection = IntegrationConnection::where('provider', $provider)->firstOrFail();
        $rules = ['enabled' => 'nullable|boolean', 'clear_credentials' => 'nullable|boolean', 'credentials' => 'sometimes|array', 'settings' => 'sometimes|array'];
        foreach ($definition['credentials'] as $key => $label) {
            $rules['credentials.'.$key] = 'nullable|string|max:4096';
        }
        foreach ($definition['settings'] as $key => $label) {
            $rules['settings.'.$key] = match ($key) {
                'currency' => 'nullable|in:EUR,BGN,USD,GBP,RON,PLN,CZK,HUF',
                'service' => 'nullable|in:gmail,microsoft',
                'products_mode' => 'nullable|in:off,validation,live',
                'mailbox' => 'nullable|email|max:255',
                'url' => 'nullable|url:https|max:2000',
                'account_id', 'location_id' => 'nullable|regex:/^[0-9]+$/|max:80',
                default => 'nullable|string|max:255',
            };
        }
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('settings', 'enabled'));
        }
        $validated = $validator->validated();
        $credentials = $request->boolean('clear_credentials') ? [] : ($connection->credentials ?? []);
        foreach (array_intersect_key($validated['credentials'] ?? [], $definition['credentials']) as $key => $value) {
            if (filled($value)) {
                $credentials[$key] = $value;
            }
        }
        $settings = array_intersect_key($validated['settings'] ?? [], $definition['settings']);
        if ($connection->runs()->where('status', 'running')->where('started_at', '>=', now()->subMinutes(20))->exists()) {
            return back()->withErrors(['integration' => 'Изчакайте текущото обновяване да завърши преди промяна на достъпа.']);
        }
        if ($credentials !== ($connection->credentials ?? []) || $settings !== ($connection->settings ?? [])) {
            $connection->state = [];
            $connection->last_success_at = null;
        }
        $connection->fill(['credentials' => $credentials, 'settings' => $settings,
            'enabled' => $request->boolean('enabled'), 'next_run_at' => null]);
        try {
            if ($provider === 'price_feed' && filled($connection->settings['url'] ?? null)) {
                app(PriceFeedConnector::class)->assertAllowedUrl($connection->settings['url']);
            }
            if ($connection->enabled && IntegrationRegistry::missing($connection) !== []) {
                throw new IntegrationException('За автоматично обновяване попълнете: '.implode(', ', IntegrationRegistry::missing($connection)).'.');
            }
        } catch (IntegrationException $exception) {
            return back()->withErrors(['integration' => $exception->getMessage()])->withInput($request->only('settings', 'enabled'));
        }
        $connection->save();
        if (! $connection->enabled) {
            $connection->runs()->where('status', 'queued')->update(['status' => 'cancelled', 'finished_at' => now(), 'message' => 'Автоматичното обновяване е изключено.']);
        }

        return back()->with('status', 'Настройките за '.$definition['name'].' са запазени.');
    }

    public function sync(string $provider, IntegrationManager $manager)
    {
        $connection = IntegrationConnection::where('provider', $provider)->firstOrFail();
        try {
            $run = $manager->enqueue($connection);
        } catch (IntegrationException $exception) {
            return back()->withErrors(['integration' => $exception->getMessage()]);
        }

        return back()->with('status', $run ? 'Обновяването е добавено в опашката. Резултатът ще се появи в историята.' : 'Вече има чакащо или активно обновяване.');
    }

    public function catalog(Request $request)
    {
        $search = mb_substr(trim((string) $request->query('q', '')), 0, 150);

        return view('admin.integrations.catalog', [
            'products' => SupplierProduct::query()->when($search !== '', fn ($query) => $query->where(fn ($query) => $query->where('code', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")))
                ->orderBy('code')->paginate(50)->withQueryString(),
            'search' => $search, 'seo' => ['title' => 'Каталог от JAR API', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function documents(Request $request)
    {
        $kind = in_array($request->query('kind'), ['orders', 'documents', 'invoices'], true) ? $request->query('kind') : null;

        return view('admin.integrations.documents', [
            'documents' => ExternalDocument::query()->when($kind, fn ($query) => $query->where('kind', $kind))->latest('synced_at')->paginate(25)->withQueryString(),
            'kind' => $kind, 'seo' => ['title' => 'Поръчки и документи от JAR', 'robots' => 'noindex,nofollow'],
        ]);
    }
}
