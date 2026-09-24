<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessOrder;
use App\Models\Conversation;
use App\Models\PricingConfiguration;
use App\Models\RepairRequest;
use App\Services\Pricing\CrossBorderReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminWorkspaceController extends Controller
{
    public function index()
    {
        return view('admin.workspace');
    }

    public function data()
    {
        $requests = RepairRequest::latest()->limit(100)->get();
        $conversations = Conversation::with(['messages' => fn ($q) => $q->latest()->limit(1)])
            ->latest('last_message_at')->limit(100)->get();
        $configurations = PricingConfiguration::whereNotNull('cross_border_data')->where('status', '!=', 'archived')->latest()->get();

        return response()->json([
            'requests' => $requests,
            'inquiries' => $conversations->map(fn ($c) => [
                'id' => $c->id, 'name' => $c->customer_name ?: 'Клиент',
                'phone' => $c->customer_phone, 'email' => $c->customer_email,
                'message' => $c->messages->first()?->content ?: '',
                'is_read' => $c->read_at && (! $c->last_message_at || $c->read_at->gte($c->last_message_at)),
                'created_at' => $c->last_message_at ?: $c->created_at, 'channel' => $c->channel,
            ]),
            'statusCounts' => RepairRequest::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'unreadCount' => Conversation::where(fn ($q) => $q->whereNull('read_at')->orWhereColumn('read_at', '<', 'last_message_at'))->count(),
            'opportunities' => $configurations->reject(fn ($c) => $c->cross_border_data['isTemplate'] ?? false)->map(fn ($c) => $this->opportunity($c))->values(),
            'templates' => $configurations->filter(fn ($c) => $c->cross_border_data['isTemplate'] ?? false)->map(fn ($c) => $this->opportunity($c))->values(),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function status(Request $request, RepairRequest $repairRequest)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(RepairRequest::STATUS_LABELS))]]);
        DB::transaction(function () use ($data, $repairRequest) {
            $repairRequest->update($data);
            $status = match ($data['status']) {
                'diagnosing' => 'diagnostics', 'awaiting_approval' => 'diagnostics', 'repairing' => 'in_progress',
                'completed' => 'ready', 'returned' => 'delivered', 'cancelled' => 'cancelled', default => 'received',
            };
            $fields = ['status' => $status];
            if ($status === 'ready') {
                $fields['ready_at'] = now();
            }
            if ($status === 'delivered') {
                $fields['picked_up_at'] = now();
            }
            BusinessOrder::where('repair_request_id', $repairRequest->id)->update($fields);
        });

        return response()->json($repairRequest->fresh());
    }

    public function read(Conversation $conversation)
    {
        $conversation->update(['read_at' => now()]);

        return response()->json(['id' => $conversation->id, 'is_read' => true]);
    }

    public function saveOpportunity(Request $request, ?PricingConfiguration $configuration = null)
    {
        $rules = [
            'name' => 'required|string|max:255', 'itemType' => 'required|in:configuration,direct-product',
            'targetMarket' => 'required|in:greece,czechia,romania', 'notes' => 'nullable|string|max:5000',
            'marketBenchmarkPrices' => 'required|array:greece,czechia,romania',
            'feeBasis' => 'sometimes|required|in:gross,net',
            'benchmarkSourceUrl' => 'nullable|url:https|max:2000',
            'benchmarkCheckedAt' => 'nullable|date_format:Y-m-d|before_or_equal:today',
            'benchmarkConfidence' => 'sometimes|integer|min:0|max:10',
            'taxVerified' => 'sometimes|boolean', 'isTemplate' => 'sometimes|boolean',
            'condition' => 'sometimes|in:new,refurbished', 'warrantyMonths' => 'sometimes|integer|min:0|max:120',
        ];
        foreach (['internalSku', 'cpu', 'gpu', 'ram', 'storage', 'motherboard', 'psu', 'caseType', 'cooling', 'targetUse'] as $key) {
            $rules[$key] = 'nullable|string|max:255';
        }
        foreach (['buyCost', 'localSalePrice', 'targetSalePrice', 'competitorPrice', 'shippingCost'] as $key) {
            $rules[$key] = 'required|numeric|min:0|max:9999999';
        }
        foreach (['greece', 'czechia', 'romania'] as $key) {
            $rules['marketBenchmarkPrices.'.$key] = 'required|numeric|min:0|max:9999999';
        }
        foreach (['marketplaceFeePercent', 'minMarginPercent'] as $key) {
            $rules[$key] = 'required|numeric|min:0|max:99';
        }
        foreach (['demandScore', 'competitionScore'] as $key) {
            $rules[$key] = 'required|numeric|min:0|max:10';
        }
        $data = $request->validate($rules);
        // A changed financial scenario always needs a new review; clients cannot set approval here.
        $data['reviewStatus'] = 'draft';
        $data['competitorPrice'] = $data['marketBenchmarkPrices'][$data['targetMarket']];
        $configuration ??= new PricingConfiguration(['sku' => 'JAR-'.Str::upper(Str::random(10)), 'status' => 'draft', 'created_by' => $request->user()->id]);
        DB::transaction(function () use ($configuration, $data, $request) {
            $configuration->fill([
                'name' => $data['name'], 'base_price_bgn' => round($data['buyCost'] * config('integrations.bgn_per_eur'), 2),
                'notes' => $data['notes'] ?? null, 'updated_by' => $request->user()->id, 'cross_border_data' => $data,
                'status' => 'draft',
            ])->save();
            $configuration->skroutzListing()->update(['approved' => false]);
        });

        return response()->json($this->opportunity($configuration));
    }

    public function review(Request $request, PricingConfiguration $configuration, CrossBorderReview $review)
    {
        $validated = $request->validate(['status' => 'required|in:draft,review,approved,rejected']);
        abort_if($configuration->status === 'archived' || ! $configuration->cross_border_data, 404);
        $data = $this->opportunity($configuration);
        if ($validated['status'] === 'approved' && ($problems = $review->problems($data, false))) {
            throw ValidationException::withMessages(['status' => $problems]);
        }
        // Capture the reviewed values, including any current imported benchmark prices.
        foreach (['id', 'configurationId', 'sku', 'createdAt', 'benchmarkSource'] as $key) {
            unset($data[$key]);
        }
        $data['reviewStatus'] = $validated['status'];
        $data['reviewedAt'] = now()->toIso8601String();
        $data['reviewedBy'] = $request->user()->id;
        DB::transaction(function () use ($configuration, $data, $request) {
            $configuration->update(['cross_border_data' => $data, 'updated_by' => $request->user()->id]);
            if ($data['reviewStatus'] !== 'approved') {
                $configuration->skroutzListing()->update(['approved' => false]);
            }
        });

        return response()->json($this->opportunity($configuration));
    }

    public function archive(PricingConfiguration $configuration)
    {
        $configuration->update(['status' => 'archived']);

        return response()->json(['archived' => true]);
    }

    private function opportunity(PricingConfiguration $configuration): array
    {
        $draft = $configuration->cross_border_data;
        $sources = [];
        foreach (['greece' => 'GR', 'czechia' => 'CZ', 'romania' => 'RO'] as $key => $code) {
            $offers = $configuration->benchmarks()->with('market')->where('is_active', true)
                ->where('collected_at', '>=', now()->subDays(config('integrations.benchmark_max_age_days')))->where('collected_at', '<=', now())
                ->whereHas('market', fn ($q) => $q->where('code', $code)->where('is_active', true))
                ->get()->filter(fn ($offer) => $offer->currency_code === $offer->market->currency_code && $offer->availability_text !== 'Synced sample benchmark');
            if ($offers->isNotEmpty()) {
                $draft['marketBenchmarkPrices'][$key] = round($offers->avg(fn ($o) => (float) $o->observed_price * ($o->price_includes_vat ? 1 : 1 + (float) $o->market->vat_rate / 100) * (float) $o->market->exchange_rate_to_bgn / config('integrations.bgn_per_eur')), 2);
                $sources[] = $code.': '.$offers->count().' оферти';
            }
        }
        $draft['competitorPrice'] = $draft['marketBenchmarkPrices'][$draft['targetMarket']] ?? 0;

        return [...$draft, 'id' => (string) $configuration->id, 'configurationId' => $configuration->id, 'sku' => $configuration->sku,
            'createdAt' => $configuration->created_at->toIso8601String(), 'benchmarkSource' => $sources ? implode(' · ', $sources) : 'Ръчно въведени оценки'];
    }
}
