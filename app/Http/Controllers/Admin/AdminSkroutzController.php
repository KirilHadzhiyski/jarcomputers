<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExternalDocument;
use App\Models\PricingConfiguration;
use App\Models\SkroutzListing;
use App\Services\Integrations\SkroutzCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminSkroutzController extends Controller
{
    public function index(SkroutzCatalog $catalog)
    {
        return view('admin.skroutz.index', [
            'configurations' => PricingConfiguration::with('skroutzListing')->where('status', '!=', 'archived')->latest()->paginate(25),
            'catalog' => $catalog, 'orders' => ExternalDocument::where('provider', 'skroutz')->latest('synced_at')->limit(25)->get(),
            'seo' => ['title' => 'Подготовка за Skroutz', 'robots' => 'noindex,nofollow'],
        ]);
    }

    public function edit(PricingConfiguration $configuration, SkroutzCatalog $catalog)
    {
        $draft = $configuration->cross_border_data ?? [];

        return view('admin.skroutz.edit', ['configuration' => $configuration, 'catalog' => $catalog,
            'data' => $configuration->skroutzListing?->attributes ?? ['description' => $configuration->notes, 'price_with_vat' => $draft['targetSalePrice'] ?? null, 'vat' => 24, 'quantity' => 0], 'seo' => ['title' => 'Конфигурация за Skroutz', 'robots' => 'noindex,nofollow']]);
    }

    public function update(Request $request, PricingConfiguration $configuration, SkroutzCatalog $catalog)
    {
        $approve = $request->boolean('approved');
        $data = $request->validate([
            'name' => 'required|string|max:255', 'sku' => ['required', 'string', 'max:100', Rule::unique('pricing_configurations')->ignore($configuration)],
            'component_summary' => 'nullable|string|max:10000', 'approved' => 'nullable|boolean',
            ...collect($catalog->rules($approve))->mapWithKeys(fn ($rules, $key) => ['product.'.$key => $rules])->all(),
        ]);
        if ($approve && ($problems = $catalog->reviewProblems($configuration, $data['product'] ?? []))) {
            throw ValidationException::withMessages(['approved' => $problems]);
        }
        DB::transaction(function () use ($data, $approve, $configuration, $request) {
            $configuration->update(['name' => $data['name'], 'sku' => $data['sku'], 'component_summary' => $data['component_summary'] ?? null,
                'status' => $approve ? 'approved' : 'draft', 'updated_by' => $request->user()->id]);
            SkroutzListing::updateOrCreate(['pricing_configuration_id' => $configuration->id], ['attributes' => $data['product'] ?? [], 'approved' => $approve]);
        });

        return back()->with('status', $approve ? 'Конфигурацията е готова за включване в каталога.' : 'Черновата е запазена.');
    }

    public function xml(SkroutzCatalog $catalog)
    {
        return response($catalog->xml())->header('Content-Type', 'application/xml; charset=UTF-8')->header('Content-Disposition', 'attachment; filename="skroutz-products.xml"');
    }

    public function batch(SkroutzCatalog $catalog)
    {
        return response()->json($catalog->batch())->header('Content-Disposition', 'attachment; filename="skroutz-inventory-preview.json"');
    }
}
