<?php

namespace App\Services\Pricing;

use Carbon\CarbonImmutable;

class CrossBorderReview
{
    public const VAT = ['greece' => 24, 'romania' => 21, 'czechia' => 21];

    public function financials(array $data): array
    {
        $gross = max(0, (float) ($data['targetSalePrice'] ?? 0));
        $net = $gross / (1 + (self::VAT[$data['targetMarket'] ?? ''] ?? 0) / 100);
        $fee = (($data['feeBasis'] ?? 'gross') === 'net' ? $net : $gross) * max(0, (float) ($data['marketplaceFeePercent'] ?? 0)) / 100;
        $cost = max(0, (float) ($data['buyCost'] ?? 0)) + max(0, (float) ($data['shippingCost'] ?? 0)) + $fee;
        $profit = $net - $cost;
        $margin = $cost > 0 ? $profit / $cost * 100 : 0;
        $benchmark = (float) ($data['marketBenchmarkPrices'][$data['targetMarket'] ?? ''] ?? 0);
        $priceScore = $benchmark <= 0 ? 12 : ($gross <= $benchmark ? 22 : ($gross <= $benchmark * 1.05 ? 10 : 0));
        $target = (float) ($data['minMarginPercent'] ?? 0);
        $score = (float) ($data['buyCost'] ?? 0) <= 0 ? 0 : min(100, round(
            ($profit > 0 ? 18 : 0) + ($margin >= $target ? 30 : ($margin >= $target - 3 ? 14 : 0)) + $priceScore
            + min(10, max(0, (float) ($data['demandScore'] ?? 0))) * 3
            + (10 - min(10, max(0, (float) ($data['competitionScore'] ?? 0)))) * 2
        ));

        return ['net' => $net, 'fee' => $fee, 'cost' => $cost, 'profit' => $profit, 'margin' => $margin, 'score' => $score];
    }

    public function problems(array $data, bool $requireApproval = true): array
    {
        $problems = [];
        if ($requireApproval && ($data['reviewStatus'] ?? 'draft') !== 'approved') {
            $problems[] = 'Липсва одобрена ревизия на анализа.';
        }
        if ($data['isTemplate'] ?? false) {
            $problems[] = 'Шаблонът трябва първо да бъде запазен като конфигурация.';
        }
        if (! trim($data['name'] ?? '') || ($data['buyCost'] ?? 0) <= 0 || ($data['targetSalePrice'] ?? 0) <= 0) {
            $problems[] = 'Попълнете име, реална себестойност и крайна цена.';
        }
        if (! array_key_exists($data['targetMarket'] ?? '', self::VAT)) {
            $problems[] = 'Изберете валиден пазар.';
        }
        $date = $data['benchmarkCheckedAt'] ?? null;
        try {
            $date = $date ? CarbonImmutable::createFromFormat('!Y-m-d', $date) : null;
        } catch (\Throwable) {
            $date = null;
        }
        if (! $date || $date->isAfter(today()) || $date->isBefore(today()->subDays(14))) {
            $problems[] = 'Benchmark датата трябва да бъде от последните 14 дни.';
        }
        if (($data['benchmarkConfidence'] ?? 0) < 6) {
            $problems[] = 'Benchmark confidence трябва да е минимум 6/10.';
        }
        $url = $data['benchmarkSourceUrl'] ?? '';
        if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https') {
            $problems[] = 'Добавете HTTPS линк към източника на benchmark цената.';
        }
        if (($data['marketBenchmarkPrices'][$data['targetMarket'] ?? ''] ?? 0) <= 0) {
            $problems[] = 'Липсва конкурентна цена за избрания пазар.';
        }
        if (! ($data['taxVerified'] ?? false)) {
            $problems[] = 'Потвърдете данъчната основа и marketplace fee.';
        }
        $financials = $this->financials($data);
        if ($financials['profit'] <= 0 || (float) ($data['minMarginPercent'] ?? 0) > $financials['margin'] + 0.001) {
            $problems[] = 'Цената трябва да покрива разходите и минималния марж.';
        }
        if ($financials['score'] < 50) {
            $problems[] = 'Оценката е твърде ниска за автоматизация; прегледайте конкурентната цена и търсенето.';
        }

        return $problems;
    }
}
