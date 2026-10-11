<?php

namespace App\Services\Integrations;

use App\Models\IntegrationConnection;

class IntegrationRegistry
{
    public static function definitions(): array
    {
        $definitions = [
            'skroutz' => [
                'name' => 'Skroutz Marketplace', 'interval' => 15,
                'description' => 'Получаване на поръчки чрез webhook и отделно валидиране или обновяване на цени и наличности през Products API.',
                'docs' => 'https://developer.skroutz.gr/smart_cart/orders_api/',
                'credentials' => ['token' => 'Orders API token', 'products_token' => 'Products API token'],
                'optional_credentials' => ['products_token'],
                'settings' => ['products_mode' => 'Автоматизация на продуктовите данни'],
                'optional_settings' => ['products_mode'],
                'help' => 'Orders token: Merchants → Services → Skroutz Marketplace. Products token: Merchants → Products → Feed Updates. Режим „Валидация“ проверява партидата без промяна; „Публикуване“ изпраща цена, наличност и активност. Поръчки не се приемат или отказват автоматично.',
            ],
            'mailbox' => [
                'name' => 'Служебна поща', 'interval' => 5,
                'description' => 'Входящите писма от Microsoft 365 или Gmail се събират като разговори с клиенти.',
                'docs' => 'https://learn.microsoft.com/en-us/graph/api/user-list-messages',
                'credentials' => ['client_id' => 'OAuth Client ID', 'client_secret' => 'OAuth Client Secret', 'refresh_token' => 'OAuth Refresh Token'],
                'settings' => ['service' => 'Пощенска услуга', 'mailbox' => 'Служебен имейл'],
                'help' => 'Свържете само служебната кутия. Права: Gmail gmail.readonly или Microsoft Mail.Read + offline_access. Първият импорт включва последните 7 дни от Inbox. Писмата остават непроменени в пощата.',
            ],
            'jar' => [
                'name' => 'JAR Computers', 'interval' => 15,
                'description' => 'Каталог, цени и наличности при доставчика, поръчки, документи и фактури за вашия JAR акаунт.',
                'docs' => 'https://api.jarcomputers.com/',
                'credentials' => ['username' => 'JAR потребител', 'password' => 'JAR парола'],
                'settings' => ['currency' => 'Валута на цените в API (потвърдете с JAR)'],
                'help' => 'Пълен каталог веднъж дневно; цени, наличности и документи на 15 минути. Наличността при доставчика се показва отделно от физическия склад в Благоевград. Данъчната основа на цените остава както е подадена от JAR.',
            ],
            'ecb' => [
                'name' => 'Европейска централна банка', 'interval' => 1440,
                'description' => 'Официални референтни валутни курсове и обновяване на валутите в ценовия анализ.',
                'docs' => 'https://www.ecb.europa.eu/stats/policy_and_exchange_rates/euro_reference_exchange_rates/html/index.en.html',
                'credentials' => [], 'settings' => [],
                'help' => 'Без API ключ. Показва датата на публикувания курс; през почивни дни остава последният работен ден.',
            ],
            'price_feed' => [
                'name' => 'Конкурентни цени', 'interval' => 60,
                'description' => 'Получаване на реални оферти от договорен JSON feed и автоматичен анализ по SKU, пазар и източник.',
                'docs' => null,
                'credentials' => ['token' => 'Bearer token (ако източникът го изисква)'],
                'optional_credentials' => ['token'],
                'settings' => ['url' => 'HTTPS адрес на ценовия feed'],
                'help' => 'Нужен е доставчик с разрешен достъп до конкурентни оферти. eMAG/Skroutz търговски акаунт не означава автоматично достъп до всички конкурентни цени. Домейнът се разрешава в сървърната конфигурация.',
            ],
            'google_reviews' => [
                'name' => 'Google Business Profile', 'interval' => 360,
                'description' => 'Отзивите за потвърдения ви бизнес обект се получават в админ панела.',
                'docs' => 'https://developers.google.com/my-business/content/review-data',
                'credentials' => ['client_id' => 'OAuth Client ID', 'client_secret' => 'OAuth Client Secret', 'refresh_token' => 'OAuth Refresh Token'],
                'settings' => ['account_id' => 'Google Business Account ID', 'location_id' => 'Google Business Location ID'],
                'help' => 'Нужни са одобрен достъп до Business Profile API и OAuth разрешение за вашия обект. Новите отзиви влизат като непубликувани записи за преглед.',
            ],
        ];
        foreach ($definitions as &$definition) {
            $definition['interval'] = max($definition['interval'], config('integrations.polling_floor_minutes', 1));
        }

        return $definitions;
    }

    public static function definition(string $provider): array
    {
        return self::definitions()[$provider] ?? throw new IntegrationException('Неподдържана интеграция.');
    }

    public static function missing(IntegrationConnection $connection): array
    {
        $definition = self::definition($connection->provider);
        $missing = [];
        foreach ($definition['credentials'] as $key => $label) {
            if (! in_array($key, $definition['optional_credentials'] ?? [], true) && blank(($connection->credentials ?? [])[$key] ?? null)) {
                $missing[] = $label;
            }
        }
        foreach ($definition['settings'] as $key => $label) {
            if (! in_array($key, $definition['optional_settings'] ?? [], true) && blank(($connection->settings ?? [])[$key] ?? null)) {
                $missing[] = $label;
            }
        }
        if ($connection->provider === 'skroutz' && in_array(($connection->settings ?? [])['products_mode'] ?? 'off', ['validation', 'live'], true)
            && blank(($connection->credentials ?? [])['products_token'] ?? null)) {
            $missing[] = 'Products API token';
        }

        return array_values(array_unique($missing));
    }
}
