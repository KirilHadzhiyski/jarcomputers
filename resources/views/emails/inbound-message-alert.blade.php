@php
    $site = config('site');
    $conversation = $conversationMessage->conversation;
@endphp
<!DOCTYPE html>
<html lang="bg">
    <body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #dbe4f0;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Ново входящо съобщение</p>
                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">{{ $conversationMessage->channel }}</h1>

                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Подател</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $conversationMessage->sender_name ?: 'Неидентифициран контакт' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Идентификатор</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $conversationMessage->sender_handle ?: 'Няма данни' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Свързана заявка</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $conversation?->repair_request_id ?: 'Няма' }}</td>
                    </tr>
                </table>

                <div style="margin-top:24px;padding:20px;border-radius:18px;background:#eff6ff;">
                    <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#1d4ed8;">Съдържание</p>
                    <p style="margin:0;line-height:1.7;">{{ $conversationMessage->content }}</p>
                </div>
            </div>

            <p style="margin:16px 0 0;font-size:13px;color:#64748b;">
                {{ $site['brand'] }} · {{ $site['support_email'] }}
            </p>
        </div>
    </body>
</html>
