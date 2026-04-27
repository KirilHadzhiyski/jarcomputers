<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingConfiguration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPricingConfigurationController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing.configurations.index', [
            'configurations' => PricingConfiguration::query()->latest()->paginate(12),
            'statusLabels' => PricingConfiguration::STATUS_LABELS,
            'seo' => [
                'title' => 'Pricing configurations',
                'description' => 'Manage internal gaming PC configurations for cross-border pricing analysis.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.pricing.configurations.create', [
            'statusLabels' => PricingConfiguration::STATUS_LABELS,
            'seo' => [
                'title' => 'New pricing configuration',
                'description' => 'Create a new gaming PC configuration for pricing analysis.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        $configuration = PricingConfiguration::query()->create($validated);

        return redirect()
            ->route('admin.pricing.configurations.edit', $configuration)
            ->with('status', 'Pricing configuration created.');
    }

    public function edit(PricingConfiguration $configuration): View
    {
        return view('admin.pricing.configurations.edit', [
            'configuration' => $configuration,
            'statusLabels' => PricingConfiguration::STATUS_LABELS,
            'seo' => [
                'title' => "Edit {$configuration->name}",
                'description' => 'Update pricing configuration details and internal notes.',
            ],
        ]);
    }

    public function update(Request $request, PricingConfiguration $configuration): RedirectResponse
    {
        $validated = $this->validated($request, $configuration);
        $validated['updated_by'] = $request->user()->id;

        $configuration->update($validated);

        return redirect()
            ->route('admin.pricing.configurations.edit', $configuration)
            ->with('status', 'Pricing configuration updated.');
    }

    public function destroy(PricingConfiguration $configuration): RedirectResponse
    {
        $configuration->delete();

        return redirect()
            ->route('admin.pricing.configurations.index')
            ->with('status', 'Pricing configuration deleted.');
    }

    private function validated(Request $request, ?PricingConfiguration $configuration = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['required', 'string', 'max:60', Rule::unique('pricing_configurations', 'sku')->ignore($configuration?->id)],
            'base_price_bgn' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(array_keys(PricingConfiguration::STATUS_LABELS))],
            'description' => ['nullable', 'string'],
            'component_summary' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
