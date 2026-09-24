<?php

namespace App\Services\Integrations;

use App\Models\IntegrationConnection;
use App\Models\PricingMarket;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EcbConnector
{
    public function __construct(private readonly ApiClient $api) {}

    public function sync(IntegrationConnection $connection): int
    {
        $response = $this->api->request()->get('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
        $this->api->check($response);
        $body = $response->body();
        if (str_contains(strtoupper($body), '<!DOCTYPE') || str_contains(strtoupper($body), '<!ENTITY')) {
            throw new IntegrationException('Невалиден XML от ЕЦБ.');
        }
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET);
            $days = $xml ? $xml->xpath('//*[local-name()="Cube"][@time]') : [];
            $date = (string) ($days[0]['time'] ?? '');
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || Carbon::parse($date)->isFuture() || Carbon::parse($date)->lt(now()->subDays(7)->startOfDay())) {
                throw new IntegrationException('ЕЦБ не върна актуална дата на курсовете.');
            }
            $rates = ['EUR' => 1.0, 'BGN' => (float) config('integrations.bgn_per_eur')];
            foreach ($days[0]->children() as $cube) {
                $currency = (string) $cube['currency'];
                $rate = (string) $cube['rate'];
                if (! preg_match('/^[A-Z]{3}$/', $currency) || ! is_numeric($rate) || (float) $rate <= 0) {
                    throw new IntegrationException('ЕЦБ върна невалиден валутен курс.');
                }
                $rates[$currency] = (float) $rate;
            }
            if (count($rates) < 3) {
                throw new IntegrationException('Отговорът от ЕЦБ не съдържа валутни курсове.');
            }
            DB::transaction(function () use ($rates, $date) {
                foreach ($rates as $currency => $rate) {
                    DB::table('exchange_rates')->updateOrInsert(['currency_code' => $currency], [
                        'units_per_eur' => $rate, 'as_of' => $date, 'fetched_at' => now(),
                    ]);
                    PricingMarket::where('currency_code', $currency)->update([
                        'exchange_rate_to_bgn' => round((float) config('integrations.bgn_per_eur') / $rate, 6),
                    ]);
                }
            });
            $connection->update(['state' => ['rate_date' => $date]]);

            return count($rates);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}
