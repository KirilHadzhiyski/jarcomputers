<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPricingSourceController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.sources.index', [
            'sources' => PricingSource::query()->with('market')->latest()->paginate(12),
            'inputTypeLabels' => PricingSource::INPUT_TYPE_LABELS,
            'seo' => [
                'title' => 'Benchmark sources',
                'description' => 'Manage benchmark sources such as bestprice.gr, Skroutz, and eMAG.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing.sources.create', [
            'markets' => PricingMarket::query()->orderBy('name')->get(),
            'inputTypeLabels' => PricingSource::INPUT_TYPE_LABELS,
            'seo' => [
                'title' => 'New source',
                'description' => 'Add a benchmark source and assign it to a market.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $source = PricingSource::query()->create($this->validated($request));

        return redirect()
            ->route('admin.pricing.sources.edit', $source)
            ->with('status', 'Source created.');
    }

    public function edit(PricingSource $source): View
    {
        return view('admin.pricing.sources.edit', [
            'source' => $source,
            'markets' => PricingMarket::query()->orderBy('name')->get(),
            'inputTypeLabels' => PricingSource::INPUT_TYPE_LABELS,
            'seo' => [
                'title' => "Edit {$source->name}",
                'description' => 'Update benchmark source settings and ingestion mode.',
            ],
        ]);
    }

    public function update(Request $request, PricingSource $source): RedirectResponse
    {
        $source->update($this->validated($request, $source));

        return redirect()
            ->route('admin.pricing.sources.edit', $source)
            ->with('status', 'Source updated.');
    }

    public function destroy(PricingSource $source): RedirectResponse
    {
        $source->delete();

        return redirect()
            ->route('admin.pricing.sources.index')
            ->with('status', 'Source deleted.');
    }

    private function validated(Request $request, ?PricingSource $source = null): array
    {
        $validated = $request->validate([
            'pricing_market_id' => ['required', 'exists:pricing_markets,id'],
            'name' => ['required', 'string', 'max:120'],
            'source_key' => ['required', 'string', 'max:120', Rule::unique('pricing_sources', 'source_key')->ignore($source?->id)],
            'base_url' => ['nullable', 'url', 'max:255'],
            'input_type' => ['required', Rule::in(array_keys(PricingSource::INPUT_TYPE_LABELS))],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
