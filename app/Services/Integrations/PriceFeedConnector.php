<?php

namespace App\Services\Integrations;

use App\Models\IntegrationConnection;
use App\Models\PricingBenchmark;
use App\Models\PricingConfiguration;
use App\Models\PricingMarket;
use App\Models\PricingSource;
use App\Services\Pricing\PricingAnalysisService;
use App\Services\Pricing\PricingBenchmarkIngestionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PriceFeedConnector
{
    public function __construct(private readonly ApiClient $api, private readonly PricingBenchmarkIngestionService $ingestion, private readonly PricingAnalysisService $analysis) {}

    public function sync(IntegrationConnection $connection): int
    {
        $url = $connection->settings['url'];
        $this->assertAllowedUrl($url);
        $request = $this->api->request();
        if (filled($connection->credentials['token'] ?? null)) {
            $request = $request->withToken($connection->credentials['token']);
        }
        $data = $this->api->json($request->get($url));
        $validator = Validator::make($data, [
            'offers' => 'present|array|max:10000', 'offers.*' => 'required|array',
            'offers.*.id' => 'required|string|max:160|distinct', 'offers.*.sku' => 'required|string|max:100',
            'offers.*.market' => 'required|string|max:20', 'offers.*.source_key' => 'required|string|max:100',
            'offers.*.price' => 'required|numeric|gt:0|max:99999999', 'offers.*.currency' => 'required|regex:/^[A-Z]{3}$/',
            'offers.*.includes_vat' => 'required|boolean', 'offers.*.collected_at' => 'required|date|before_or_equal:now|after_or_equal:'.now()->subDays(config('integrations.benchmark_max_age_days'))->toIso8601String(),
            'offers.*.url' => 'required|url:https|max:2000', 'offers.*.title' => 'nullable|string|max:255',
            'offers.*.availability' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            throw new IntegrationException('Feed-ът съдържа невалидни или остарели оферти. Няма записани промени. Проверете договора за формата.');
        }
        $offers = $validator->validated()['offers'];

        return DB::transaction(function () use ($offers) {
            $pairs = [];
            foreach ($offers as $offer) {
                $configuration = PricingConfiguration::where('sku', $offer['sku'])->first();
                $market = PricingMarket::where('code', $offer['market'])->where('is_active', true)->first();
                $source = $market ? PricingSource::where('pricing_market_id', $market->id)->where('source_key', $offer['source_key'])->where('is_active', true)->first() : null;
                if (! $configuration || ! $market || ! $source || $market->currency_code !== $offer['currency']) {
                    throw new IntegrationException('Оферта няма съответстващи SKU, активен пазар, източник или валута. Няма записани промени.');
                }
                $attributes = $this->ingestion->attributesFromPayload([
                    'pricing_configuration_id' => $configuration->id, 'pricing_market_id' => $market->id,
                    'pricing_source_id' => $source->id, 'observed_price' => $offer['price'],
                    'currency_code' => $offer['currency'], 'price_includes_vat' => $offer['includes_vat'],
                    'collected_at' => $offer['collected_at'], 'product_url' => $offer['url'],
                    'product_title' => $offer['title'] ?? $configuration->name, 'competitor_name' => $source->name,
                    'availability_text' => $offer['availability'] ?? null, 'input_method' => 'api',
                ]);
                PricingBenchmark::updateOrCreate(['pricing_source_id' => $source->id, 'external_id' => $offer['id']], $attributes);
                $pairs[$configuration->id.':'.$market->id] = [$configuration, $market];
            }
            foreach ($pairs as [$configuration, $market]) {
                $this->analysis->analyze($configuration, $market);
            }

            return count($offers);
        });
    }

    public function assertAllowedUrl(string $url): void
    {
        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['fragment'])
            || ($parts['port'] ?? 443) !== 443 || filter_var($host, FILTER_VALIDATE_IP)
            || ! in_array($host, config('integrations.feed_hosts', []), true)) {
            throw new IntegrationException('Feed-ът трябва да използва HTTPS и домейн, разрешен в INTEGRATION_FEED_HOSTS.');
        }
    }
}
