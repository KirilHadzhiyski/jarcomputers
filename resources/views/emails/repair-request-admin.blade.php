@php($site = config('site'))
<!DOCTYPE html>
<html lang="bg">
    <body style="margin:0;background:#f4f7fb;font-family:Arial,sans-serif;color:#0f172a;">
        <div style="max-width:680px;margin:0 auto;padding:32px 20px;">
            <div style="background:#ffffff;border:1px solid #dbe4f0;border-radius:24px;padding:32px;">
                <p style="margin:0 0 12px;font-size:12px;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;">Нова заявка за ремонт</p>
                <h1 style="margin:0 0 24px;font-size:28px;line-height:1.2;">{{ $repairRequest->name }}</h1>

                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Телефон</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->phone }}</td>
                    </tr>
                    @if ($repairRequest->email)
                        <tr>
                            <td style="padding:10px 0;color:#64748b;">Имейл</td>
                            <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->email }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Град</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->city ?: 'Не е посочен' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Модел</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->model ?: 'Не е посочен' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Предпочитан контакт</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->preferred_contact }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0;color:#64748b;">Източник</td>
                        <td style="padding:10px 0;text-align:right;font-weight:700;">{{ $repairRequest->source_page ?: '/' }}</td>
                    </tr>
                </table>

                <div style="margin-top:24px;padding:20px;border-radius:18px;background:#eff6ff;">
                    <p style="margin:0 0 8px;font-size:12px;letter-spacing:0.16em;text-transform:uppercase;color:#1d4ed8;">Описание на проблема</p>
                    <p style="margin:0;line-height:1.7;">{{ $repairRequest->issue }}</p>
                </div>
            </div>

            <p style="margin:16px 0 0;font-size:13px;color:#64748b;">
                {{ $site['brand'] }} · {{ $site['phone'] }} · {{ $site['support_email'] }}
            </p>
        </div>
    </body>
</html>
