<?php

namespace App\Services\Integrations;

use App\Models\PricingConfiguration;
use App\Services\Pricing\CrossBorderReview;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SkroutzCatalog
{
    public const FIELDS = [
        'link' => 'Публична продуктова страница (HTTPS)', 'image' => 'Публична продуктова снимка (HTTPS)',
        'category' => 'Категория', 'manufacturer' => 'Производител / марка', 'mpn' => 'Код на производителя (MPN)',
        'ean' => 'EAN / GTIN', 'description' => 'Описание', 'price_with_vat' => 'Крайна цена в EUR с ДДС',
        'vat' => 'Потвърдена ставка ДДС %', 'quantity' => 'Реално количество за продажба',
        'availability' => 'Срок за доставка', 'weight' => 'Тегло в грамове',
    ];

    public const AVAILABILITY = ['Available', 'Delivery 1 to 3 days', 'Delivery 4 to 10 days', 'Delivery 10 to 30 days'];

    public function rules(bool $complete = true): array
    {
        $required = $complete ? 'required' : 'nullable';

        return [
            'link' => "$required|url:https|max:1000", 'image' => "$required|url:https|max:400",
            'category' => "$required|string|max:250", 'manufacturer' => "$required|string|max:100",
            'mpn' => "$required|string|max:80", 'ean' => [$required, 'regex:/^([0-9]{8}|[0-9]{12}|[0-9]{13}|[0-9]{14})$/'],
            'description' => "$required|string|max:10000", 'price_with_vat' => "$required|numeric|gt:0|max:9999999|decimal:0,2",
            'vat' => "$required|numeric|min:0|max:30", 'quantity' => "$required|integer|min:0|max:1000000",
            'availability' => [$required, Rule::in(self::AVAILABILITY)],
            'weight' => 'nullable|numeric|gt:0|max:1000000',
        ];
    }

    public function missing(PricingConfiguration $configuration): array
    {
        $listing = $configuration->skroutzListing;
        $validator = Validator::make($listing?->attributes ?? [], $this->rules());
        $missing = array_map(fn ($key) => self::FIELDS[$key] ?? $key, array_keys($validator->errors()->messages()));
        if (! $configuration->sku) {
            $missing[] = 'SKU';
        }
        if ($configuration->status !== 'approved') {
            $missing[] = 'Одобрена конфигурация';
        }
        if (! $listing?->approved) {
            $missing[] = 'Одобрена публикация за Skroutz';
        }
        if ($configuration->cross_border_data) {
            $missing = [...$missing, ...$this->reviewProblems($configuration, $listing?->attributes ?? [])];
        }

        return $missing;
    }

    public function reviewProblems(PricingConfiguration $configuration, array $product): array
    {
        $data = $configuration->cross_border_data;
        if (! $data) {
            return [];
        }
        $problems = app(CrossBorderReview::class)->problems($data);
        if ($data['targetMarket'] !== 'greece') {
            $problems[] = 'За Skroutz прегледайте анализ за пазара в Гърция.';
        }
        if (abs((float) ($product['price_with_vat'] ?? 0) - (float) $data['targetSalePrice']) > 0.011 || (float) ($product['vat'] ?? 0) !== 24.0) {
            $problems[] = 'Цената и ДДС за Skroutz трябва да съвпадат с одобрения анализ за Гърция.';
        }

        return $problems;
    }

    public function ready()
    {
        return PricingConfiguration::with('skroutzListing')->where('status', 'approved')->orderBy('id')->get()
            ->filter(fn ($configuration) => $this->missing($configuration) === []);
    }

    public function xml(): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $root = $xml->appendChild($xml->createElement('mywebstore'));
        $root->appendChild($xml->createElement('created_at'))->appendChild($xml->createTextNode(now()->format('Y-m-d H:i')));
        $products = $root->appendChild($xml->createElement('products'));
        foreach ($this->ready() as $configuration) {
            $data = $configuration->skroutzListing->attributes;
            if ((int) $data['quantity'] === 0) {
                continue;
            }
            $product = $products->appendChild($xml->createElement('product'));
            foreach (['id' => $configuration->sku, 'name' => $configuration->name, ...$data] as $key => $value) {
                if (! in_array($key, ['id', 'name', ...array_keys(self::FIELDS)], true) || $value === null || $value === '') {
                    continue;
                }
                if (in_array($key, ['price_with_vat', 'vat'])) {
                    $value = number_format((float) $value, 2, '.', '');
                }
                $product->appendChild($xml->createElement($key))->appendChild($xml->createTextNode(strip_tags((string) $value)));
            }
        }

        return $xml->saveXML();
    }

    public function batch(): array
    {
        return ['data' => $this->ready()->map(fn ($c) => [
            'product_id' => $c->sku, 'quantity' => (int) $c->skroutzListing->attributes['quantity'],
            'enabled' => (int) $c->skroutzListing->attributes['quantity'] > 0,
            'price' => (int) round((float) $c->skroutzListing->attributes['price_with_vat'] * 100),
        ])->values()->all()];
    }
}
