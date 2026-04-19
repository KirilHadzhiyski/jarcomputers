@php($site = config('site'))
<!DOCTYPE html>
<html lang="bg">
    <body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #dbe4f0;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Потвърждение</p>
                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">Код за активиране на профила</h1>
                <p style="margin:0 0 18px;line-height:1.7;">
                    Здравейте, {{ $user->name }}. За да активирате профила си в клиентския портал, въведете следния код:
                </p>

                <div style="padding:24px;border-radius:18px;background:#eff6ff;text-align:center;">
                    <p style="margin:0 0 10px;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#1d4ed8;">Вашият код</p>
                    <p style="margin:0;font-size:36px;font-weight:700;letter-spacing:0.2em;color:#0f172a;">{{ $code }}</p>
                </div>

                <p style="margin:20px 0 0;line-height:1.7;">
                    Кодът е валиден 15 минути. Ако не сте поискали регистрация, просто игнорирайте това съобщение.
                </p>

                <p style="margin:20px 0 0;line-height:1.7;color:#64748b;">
                    {{ $site['brand'] }} · {{ $site['phone'] }} · {{ $site['support_email'] }}
                </p>
            </div>
        </div>
    </body>
</html>
