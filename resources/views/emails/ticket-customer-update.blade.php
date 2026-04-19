@php($site = config('site'))
<!DOCTYPE html>
<html lang="bg">
    <body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #dbe4f0;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Поръчка #{{ $ticket->id }}</p>
                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">Имате ново обновление</h1>
                <p style="margin:0 0 18px;line-height:1.7;">
                    {{ $update->message }}
                </p>

                <div style="padding:20px;border-radius:18px;background:#eff6ff;">
                    <p style="margin:0 0 8px;"><strong>Тема:</strong> {{ $ticket->subject }}</p>
                    <p style="margin:0 0 8px;"><strong>Статус:</strong> {{ $ticket->statusLabel() }}</p>
                    <p style="margin:0;"><strong>Модел:</strong> {{ $ticket->device_model ?: 'Не е посочен' }}</p>
                </div>

                <p style="margin:20px 0 0;line-height:1.7;">
                    Можете да влезете в клиентския си профил, за да проследите историята и следващите стъпки по поръчката.
                </p>

                <p style="margin:20px 0 0;line-height:1.7;color:#64748b;">
                    {{ $site['brand'] }} · {{ $site['phone'] }} · {{ $site['support_email'] }}
                </p>
            </div>
        </div>
    </body>
</html>
