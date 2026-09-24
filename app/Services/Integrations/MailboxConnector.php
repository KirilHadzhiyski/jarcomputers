<?php

namespace App\Services\Integrations;

use App\Models\IntegrationConnection;
use App\Services\Communications\ConversationService;
use Illuminate\Support\Carbon;

class MailboxConnector
{
    public function __construct(private readonly ApiClient $api, private readonly ConversationService $conversations) {}

    public function sync(IntegrationConnection $connection): int
    {
        $until = now()->utc();
        $since = isset($connection->state['mail_until']) ? Carbon::parse($connection->state['mail_until'])->subMinutes(5) : now()->subDays(7)->utc();
        $count = match ($connection->settings['service']) {
            'gmail' => $this->gmail($connection, $since, $until),
            'microsoft' => $this->microsoft($connection, $since, $until),
            default => throw new IntegrationException('Изберете поддържана пощенска услуга.'),
        };
        $connection->update(['state' => ['mail_until' => $until->toIso8601String()]]);

        return $count;
    }

    private function gmail(IntegrationConnection $connection, Carbon $since, Carbon $until): int
    {
        $token = $this->api->googleToken($connection->credentials);
        $base = 'https://gmail.googleapis.com/gmail/v1/users/'.rawurlencode($connection->settings['mailbox']).'/messages';
        $next = null;
        $seen = [];
        $count = 0;
        do {
            $page = $this->api->json($this->api->request()->withToken($token)->get($base, array_filter([
                'labelIds' => 'INBOX', 'q' => 'after:'.$since->timestamp.' before:'.$until->timestamp,
                'maxResults' => 100, 'pageToken' => $next,
            ])));
            if (! array_key_exists('messages', $page) && ! array_key_exists('resultSizeEstimate', $page)) {
                throw new IntegrationException('Gmail върна непълен списък с писма.');
            }
            foreach ($page['messages'] ?? [] as $item) {
                if (empty($item['id'])) {
                    throw new IntegrationException('Gmail върна писмо без идентификатор.');
                }
                $mail = $this->api->json($this->api->request()->withToken($token)->get($base.'/'.rawurlencode($item['id']), ['format' => 'full']));
                $headers = collect(data_get($mail, 'payload.headers', []))->mapWithKeys(fn ($header) => [strtolower($header['name']) => $header['value']]);
                $from = $headers->get('from', '');
                preg_match('/<?([^<>\s]+@[^<>\s]+)>?/', $from, $match);
                $email = trim($match[1] ?? '');
                $name = trim(str_replace($match[0] ?? '', '', $from), " \"'<>");
                $body = $this->gmailBody($mail['payload'] ?? []) ?: $this->gmailBody($mail['payload'] ?? [], true);
                $count += $this->store($connection, (string) ($mail['id'] ?? $item['id']), (string) ($mail['threadId'] ?? $item['id']), $name, $email,
                    $headers->get('subject', ''), $body, isset($mail['internalDate']) ? Carbon::createFromTimestampMsUTC((int) $mail['internalDate']) : null);
            }
            $next = $page['nextPageToken'] ?? null;
            $this->checkPagination($next, $seen);
        } while ($next);

        return $count;
    }

    private function microsoft(IntegrationConnection $connection, Carbon $since, Carbon $until): int
    {
        $credentials = $connection->credentials;
        $auth = $this->api->json($this->api->request()->asForm()->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
            'client_id' => $credentials['client_id'], 'client_secret' => $credentials['client_secret'],
            'refresh_token' => $credentials['refresh_token'], 'grant_type' => 'refresh_token',
        ]));
        if (empty($auth['access_token']) || ! is_string($auth['access_token'])) {
            throw new IntegrationException('Microsoft не върна валиден token. Свържете акаунта отново.');
        }
        if (filled($auth['refresh_token'] ?? null)) {
            $connection->update(['credentials' => [...$credentials, 'refresh_token' => $auth['refresh_token']]]);
        }
        $base = 'https://graph.microsoft.com/v1.0/users/'.rawurlencode($connection->settings['mailbox']).'/mailFolders/inbox/messages';
        $next = $base.'?'.http_build_query([
            '$top' => 100, '$select' => 'id,conversationId,from,subject,body,receivedDateTime',
            '$filter' => 'receivedDateTime ge '.$since->toIso8601ZuluString().' and receivedDateTime le '.$until->toIso8601ZuluString(),
            '$orderby' => 'receivedDateTime asc',
        ]);
        $seen = [];
        $count = 0;
        do {
            // A server-returned nextLink must not move the bearer token to another origin.
            if (! str_starts_with($next, $base.'?')) {
                throw new IntegrationException('Microsoft върна непозволен адрес за следваща страница.');
            }
            $page = $this->api->json($this->api->request()->withToken($auth['access_token'])->withHeaders(['Prefer' => 'outlook.body-content-type="text"'])->get($next));
            if (! isset($page['value']) || ! is_array($page['value'])) {
                throw new IntegrationException('Microsoft върна непълен списък с писма.');
            }
            foreach ($page['value'] as $mail) {
                if (empty($mail['id']) || empty($mail['receivedDateTime'])) {
                    throw new IntegrationException('Microsoft върна непълно писмо.');
                }
                $body = (string) data_get($mail, 'body.content', '');
                if (strtolower((string) data_get($mail, 'body.contentType')) === 'html') {
                    $body = html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
                $count += $this->store($connection, $mail['id'], $mail['conversationId'] ?? $mail['id'], data_get($mail, 'from.emailAddress.name', ''),
                    data_get($mail, 'from.emailAddress.address', ''), $mail['subject'] ?? '', $body, Carbon::parse($mail['receivedDateTime']));
            }
            $next = $page['@odata.nextLink'] ?? null;
            $this->checkPagination($next, $seen);
        } while ($next);

        return $count;
    }

    private function store(IntegrationConnection $connection, string $id, string $thread, string $name, string $email, string $subject, string $body, ?Carbon $received): int
    {
        if (! $received || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new IntegrationException('Писмо няма валидни подател или дата. Импортът не е завършен.');
        }
        $namespace = $connection->settings['service'].':'.mb_strtolower($connection->settings['mailbox']);
        $stored = $this->conversations->storeInboundMessage('email', [
            'external_conversation_id' => hash('sha256', $namespace.':'.$thread),
            'external_user_id' => $email, 'customer_name' => mb_substr($name ?: $email, 0, 120),
            'customer_email' => $email, 'customer_phone' => null, 'sender_handle' => mb_substr($email, 0, 120),
            'provider_message_id' => hash('sha256', $namespace.':'.$id),
            'content' => mb_substr($subject."\n\n".$body, 0, 20000), 'received_at' => $received,
            'meta' => ['subject' => mb_substr($subject, 0, 255)],
        ], ['provider' => $connection->settings['service'], 'message_id' => $id]);

        return $stored->wasRecentlyCreated ? 1 : 0;
    }

    private function gmailBody(array $part, bool $allowHtml = false): string
    {
        if (($part['mimeType'] ?? '') === 'text/plain' && isset($part['body']['data'])) {
            return base64_decode(strtr($part['body']['data'], '-_', '+/'), true) ?: '';
        }
        $text = [];
        foreach ($part['parts'] ?? [] as $child) {
            $body = $this->gmailBody($child, $allowHtml);
            if ($body !== '') {
                $text[] = $body;
            }
        }
        if ($text !== []) {
            return implode("\n", $text);
        }
        if ($allowHtml && ($part['mimeType'] ?? '') === 'text/html') {
            return html_entity_decode(strip_tags(base64_decode(strtr($part['body']['data'] ?? '', '-_', '+/'), true) ?: ''), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return '';
    }

    private function checkPagination(?string $next, array &$seen): void
    {
        if ($next && (isset($seen[$next]) || count($seen) >= 100)) {
            throw new IntegrationException('Пощата върна повтаряща се или прекалено дълга пагинация.');
        }
        if ($next) {
            $seen[$next] = true;
        }
    }
}
