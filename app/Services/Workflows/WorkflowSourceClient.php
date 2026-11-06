<?php

namespace App\Services\Workflows;

use App\Models\Workflow;
use App\Services\Integrations\ApiClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use JsonException;

class WorkflowSourceClient
{
    public function __construct(private readonly ApiClient $api) {}

    public function collect(Workflow $workflow): array
    {
        if (! in_array($workflow->source_type, ['rest_api', 'graphql', 'rss', 'file_feed'], true)) {
            throw new WorkflowExecutionException('Този тип източник още няма разрешен изпълним конектор. Web scraping се включва само след отделна проверка на източника.');
        }

        $url = $this->validatedUrl($workflow);
        $request = $this->authenticatedRequest($workflow);
        $collectConfig = $workflow->steps->firstWhere('type', 'collect')?->configuration ?? [];

        if ($workflow->source_type === 'graphql' && blank($collectConfig['query'] ?? null)) {
            throw new WorkflowExecutionException('GraphQL източникът изисква query в конфигурацията на collect стъпката.');
        }

        $response = $workflow->source_type === 'graphql'
            ? $request->post($url, [
                'query' => $collectConfig['query'] ?? null,
                'variables' => $collectConfig['variables'] ?? new \stdClass,
            ])
            : $request->get($url);

        $this->api->check($response);
        $body = $response->body();
        $this->assertResponseSize($body, $response);

        $items = match ($workflow->source_type) {
            'rss' => $this->rssItems($body),
            'file_feed' => $this->fileItems($body, $response, $collectConfig),
            default => $this->jsonItems($body, $collectConfig),
        };

        $max = (int) config('workflows.max_records_per_run', 5000);
        if (count($items) > $max) {
            throw new WorkflowQuotaException("Източникът върна повече от максимално разрешените {$max} записа за едно изпълнение.");
        }

        return array_values(array_map(fn (array $item) => $this->normalize($item, $collectConfig, $url), $items));
    }

    private function authenticatedRequest(Workflow $workflow): PendingRequest
    {
        $request = $this->api->request()->withHeaders([
            'User-Agent' => 'JAR-Workflow-Engine/1.0',
        ]);
        $credentials = $workflow->source_credentials ?? [];

        return match ($workflow->source_auth_type) {
            'bearer' => filled($credentials['token'] ?? null)
                ? $request->withToken($credentials['token'])
                : throw new WorkflowExecutionException('Липсва Bearer token за източника.'),
            'basic' => filled($credentials['username'] ?? null) && array_key_exists('password', $credentials)
                ? $request->withBasicAuth($credentials['username'], $credentials['password'])
                : throw new WorkflowExecutionException('Липсват потребител или парола за източника.'),
            'none', null, '' => $request,
            default => throw new WorkflowExecutionException('Неподдържан метод за удостоверяване на източника.'),
        };
    }

    private function validatedUrl(Workflow $workflow): string
    {
        $url = trim((string) $workflow->source_url);
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowed = config('workflows.allowed_hosts', []);

        if (($parts['scheme'] ?? null) !== 'https' || $host === '') {
            throw new WorkflowExecutionException('Източникът трябва да използва валиден HTTPS адрес.');
        }
        if (! in_array($host, $allowed, true)) {
            throw new WorkflowExecutionException("Домейнът {$host} не е разрешен в WORKFLOW_SOURCE_HOSTS.");
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new WorkflowExecutionException('Данни за достъп не се поставят в URL адреса.');
        }

        return $url;
    }

    private function assertResponseSize(string $body, Response $response): void
    {
        $max = (int) config('workflows.max_response_bytes', 5_000_000);
        $declared = (int) $response->header('Content-Length', 0);
        if ($declared > $max || strlen($body) > $max) {
            throw new WorkflowExecutionException('Отговорът от източника надвишава разрешения размер.');
        }
    }

    private function jsonItems(string $body, array $config): array
    {
        try {
            $decoded = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new WorkflowExecutionException('Източникът не върна валиден JSON документ.');
        }
        if (is_array($decoded) && ! empty($decoded['errors'])) {
            throw new WorkflowExecutionException('GraphQL/API източникът върна грешки вместо надежден резултат.');
        }

        $items = filled($config['items_path'] ?? null)
            ? data_get($decoded, $config['items_path'])
            : $this->discoverItems($decoded);

        if (! is_array($items)) {
            throw new WorkflowExecutionException('Не е намерен масив със записи. Задайте items_path в стъпката за събиране.');
        }

        return array_values(array_filter($items, 'is_array'));
    }

    private function discoverItems(mixed $decoded): mixed
    {
        if (is_array($decoded) && array_is_list($decoded)) {
            return $decoded;
        }

        foreach (['items', 'products', 'offers', 'results', 'data.items', 'data.products', 'data.results', 'data'] as $path) {
            $candidate = data_get($decoded, $path);
            if (is_array($candidate) && array_is_list($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function rssItems(string $body): array
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($body, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
            if ($xml === false) {
                throw new WorkflowExecutionException('RSS източникът не върна валиден XML документ.');
            }
            $nodes = $xml->channel?->item ?? $xml->entry ?? [];
            $items = [];
            foreach ($nodes as $node) {
                $items[] = [
                    'id' => (string) ($node->guid ?? $node->id ?? ''),
                    'title' => (string) ($node->title ?? ''),
                    'description' => (string) ($node->description ?? $node->summary ?? ''),
                    'category' => (string) ($node->category ?? ''),
                    'url' => (string) ($node->link['href'] ?? $node->link ?? ''),
                    'published_at' => (string) ($node->pubDate ?? $node->published ?? $node->updated ?? ''),
                ];
            }

            return $items;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private function fileItems(string $body, Response $response, array $config): array
    {
        $contentType = strtolower((string) $response->header('Content-Type'));
        if (str_contains($contentType, 'json') || str_starts_with(ltrim($body), '[') || str_starts_with(ltrim($body), '{')) {
            return $this->jsonItems($body, $config);
        }

        $delimiter = (string) ($config['delimiter'] ?? ',');
        if (! in_array($delimiter, [',', ';', "\t", '|'], true)) {
            throw new WorkflowExecutionException('Неподдържан разделител за файловия feed.');
        }
        $lines = preg_split('/\R/', trim($body));
        $header = str_getcsv((string) array_shift($lines), $delimiter);
        if ($header === [] || count($header) > 100) {
            throw new WorkflowExecutionException('Файловият feed няма валиден заглавен ред.');
        }

        $items = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = str_getcsv($line, $delimiter);
            if (count($values) !== count($header)) {
                throw new WorkflowExecutionException('Файловият feed съдържа ред с неочакван брой колони.');
            }
            $items[] = array_combine($header, $values);
        }

        return $items;
    }

    private function normalize(array $item, array $config, string $sourceUrl): array
    {
        $map = array_merge([
            'external_id' => ['id', 'external_id', 'offer_id'],
            'sku' => ['sku', 'code', 'product_code'],
            'ean' => ['ean', 'barcode'],
            'gtin' => ['gtin'],
            'mpn' => ['mpn', 'manufacturer_part_number'],
            'title' => ['title', 'name', 'product_name'],
            'category' => ['category', 'category_name'],
            'price' => ['price', 'final_price', 'sale_price'],
            'currency_code' => ['currency_code', 'currency'],
            'availability' => ['availability', 'stock_status'],
            'rating' => ['rating', 'review_rating'],
            'promotion' => ['promotion', 'discount_label'],
            'source_url' => ['url', 'product_url', 'link'],
        ], Arr::wrap($config['field_map'] ?? []));

        $normalized = [];
        foreach ($map as $field => $paths) {
            foreach (Arr::wrap($paths) as $path) {
                $value = data_get($item, $path);
                if ($value !== null && $value !== '') {
                    $normalized[$field] = is_scalar($value) ? trim((string) $value) : null;
                    break;
                }
            }
        }
        foreach (['external_id', 'sku', 'ean', 'gtin', 'mpn', 'title', 'category', 'availability', 'promotion'] as $field) {
            if (isset($normalized[$field])) {
                $normalized[$field] = mb_substr(trim(strip_tags((string) $normalized[$field])), 0, $field === 'ean' || $field === 'gtin' ? 32 : 255);
            }
        }
        $normalized['title'] = $normalized['title'] ?? $normalized['sku'] ?? $normalized['external_id'] ?? 'Запис без заглавие';
        $normalized['currency_code'] = isset($normalized['currency_code']) ? strtoupper(mb_substr($normalized['currency_code'], 0, 3)) : null;
        $normalized['price'] = $this->numeric($normalized['price'] ?? null);
        $normalized['rating'] = $this->numeric($normalized['rating'] ?? null);
        $candidateUrl = filter_var($normalized['source_url'] ?? null, FILTER_VALIDATE_URL) ?: null;
        $normalized['source_url'] = $candidateUrl && parse_url($candidateUrl, PHP_URL_SCHEME) === 'https' ? $candidateUrl : $sourceUrl;
        $normalized['data'] = $this->redact($item);
        $identity = $normalized['external_id'] ?? $normalized['sku'] ?? $normalized['ean'] ?? $normalized['gtin'] ?? json_encode($normalized['data']);
        $normalized['fingerprint'] = hash('sha256', (string) $identity);

        return $normalized;
    }

    private function numeric(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $normalized = str_replace([' ', ','], ['', '.'], (string) $value);

        if (! is_numeric($normalized)) {
            return null;
        }
        $number = (float) $normalized;

        return is_finite($number) && abs($number) <= 9_999_999_999 ? $number : null;
    }

    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (preg_match('/token|secret|password|authorization|cookie/i', (string) $key)) {
                $data[$key] = '[redacted]';
            } elseif (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }

        return $data;
    }
}
