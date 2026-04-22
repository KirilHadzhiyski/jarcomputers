@php($site = config('site'))
<!DOCTYPE html>
<html lang="bg">
    <body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #dbe4f0;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Потвърждение</p>
                <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">Получихме вашата заявка</h1>
                <p style="margin:0 0 18px;line-height:1.7;">
                    Здравейте, {{ $repairRequest->name }}. Получихме заявката ви за ремонт и ще се свържем с вас в рамките на работното време.
                </p>

                <div style="padding:20px;border-radius:18px;background:#eff6ff;">
                    <p style="margin:0 0 10px;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#1d4ed8;">Резюме</p>
                    <p style="margin:0 0 8px;"><strong>Модел:</strong> {{ $repairRequest->model ?: 'Не е посочен' }}</p>
                    <p style="margin:0 0 8px;"><strong>Предпочитан контакт:</strong> {{ $repairRequest->preferredContactLabel() }}</p>
                    <p style="margin:0;"><strong>Описание:</strong> {{ $repairRequest->issue }}</p>
                </div>

                <p style="margin:20px 0 0;line-height:1.7;">
                    Ако искате да добавите детайли, можете да ни пишете на {{ $site['support_email'] }} или да се обадите на {{ $site['phone'] }}.
                </p>
            </div>
        </div>
    </body>
</html>
