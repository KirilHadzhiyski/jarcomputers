<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Services\Pricing\PricingBenchmarkIngestionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPricingBenchmarkController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.benchmarks.index', [
            'benchmarks' => PricingBenchmark::query()
                ->with(['configuration', 'market', 'source'])
                ->latest('collected_at')
                ->paginate(15),
            'inputMethodLabels' => PricingBenchmark::INPUT_METHOD_LABELS,
            'seo' => [
                'title' => 'Benchmarks',
                'description' => 'Track competitor benchmark entries from manual admin input and scraper syncs.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing.benchmarks.create', [
            'configurations' => PricingConfiguration::query()->orderBy('name')->get(),
            'markets' => PricingMarket::query()->orderBy('name')->get(),
            'sources' => PricingSource::query()->with('market')->orderBy('name')->get(),
            'inputMethodLabels' => PricingBenchmark::INPUT_METHOD_LABELS,
            'seo' => [
                'title' => 'New benchmark',
                'description' => 'Add a competitor benchmark for a specific configuration and market.',
            ],
        ]);
    }

    public function store(Request $request, PricingBenchmarkIngestionService $ingestionService): RedirectResponse
    {
        $validated = $this->validated($request);
        $this->assertSourceMatchesMarket($validated['pricing_source_id'], $validated['pricing_market_id']);

        $benchmark = $ingestionService->ingest($validated, $request->user());

        return redirect()
            ->route('admin.pricing.benchmarks.edit', $benchmark)
            ->with('status', 'Benchmark created.');
    }

    public function edit(PricingBenchmark $benchmark): View
    {
        return view('admin.pricing.benchmarks.edit', [
            'benchmark' => $benchmark,
            'configurations' => PricingConfiguration::query()->orderBy('name')->get(),
            'markets' => PricingMarket::query()->orderBy('name')->get(),
            'sources' => PricingSource::query()->with('market')->orderBy('name')->get(),
            'inputMethodLabels' => PricingBenchmark::INPUT_METHOD_LABELS,
            'seo' => [
                'title' => 'Edit benchmark',
                'description' => 'Update benchmark pricing and source metadata.',
            ],
        ]);
    }

    public function update(Request $request, PricingBenchmark $benchmark, PricingBenchmarkIngestionService $ingestionService): RedirectResponse
    {
        $validated = $this->validated($request);
        $this->assertSourceMatchesMarket($validated['pricing_source_id'], $validated['pricing_market_id']);

        $benchmark->update($ingestionService->attributesFromPayload($validated, $request->user()));

        return redirect()
            ->route('admin.pricing.benchmarks.edit', $benchmark)
            ->with('status', 'Benchmark updated.');
    }

    public function destroy(PricingBenchmark $benchmark): RedirectResponse
    {
        $benchmark->delete();

        return redirect()
            ->route('admin.pricing.benchmarks.index')
            ->with('status', 'Benchmark deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'pricing_configuration_id' => ['required', 'exists:pricing_configurations,id'],
            'pricing_market_id' => ['required', 'exists:pricing_markets,id'],
            'pricing_source_id' => ['required', 'exists:pricing_sources,id'],
            'observed_price' => ['required', 'numeric', 'gt:0'],
            'currency_code' => ['required', 'string', 'max:8'],
            'price_includes_vat' => ['nullable', 'boolean'],
            'availability_text' => ['nullable', 'string', 'max:120'],
            'competitor_name' => ['nullable', 'string', 'max:120'],
            'product_title' => ['nullable', 'string', 'max:160'],
            'product_url' => ['nullable', 'url', 'max:2000'],
            'input_method' => ['required', Rule::in(array_keys(PricingBenchmark::INPUT_METHOD_LABELS))],
            'is_active' => ['nullable', 'boolean'],
            'collected_at' => ['nullable', 'date'],
        ]);

        $validated['price_includes_vat'] = $request->boolean('price_includes_vat');
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }

    private function assertSourceMatchesMarket(int|string $sourceId, int|string $marketId): void
    {
        $source = PricingSource::query()->findOrFail($sourceId);

        abort_if((int) $source->pricing_market_id !== (int) $marketId, 422, 'Selected source does not belong to the chosen market.');
    }
}
