# Качване на JAR Computers в cPanel

Подготвен пакет: Laravel + оригиналния React админ + автоматизации + подготовка за Skroutz. Създаден на 23.09.2026 г. Това е PHP приложение с база данни; старият статичен ZIP не може да изпълнява импортите.

## 1. Какво има в архива

```text
jar-app/                  частно приложение, vendor/, миграции и примерен .env
public_html/              публични файлове, index.php, .htaccess, build/, admin-build/
UPLOAD-CPANEL.md           тези инструкции
ADMIN-AUTOMATION.md        поведение и API настройки
RELEASE.json               информация за изданието
```

Архивът включва production PHP зависимости и компилиран JavaScript/CSS. На хостинга не са нужни Node.js, npm или Composer за първото качване. Няма локална база, пароли, API ключове, логове, node_modules или Git история. `.env.example` съдържа празни полета за вашите настройки.

## 2. Проверка на хостинга

- PHP **8.4** за сайта **и** за Cron/Terminal. `composer.lock` включва зависимости, изискващи 8.4, въпреки че основният Laravel пакет поддържа и по-стари версии.
- MySQL 8 или съвместима поддържана MariaDB, отделна база и потребител с права върху нея.
- Apache/LiteSpeed с `.htaccess`, HTTPS сертификат за `jarbl.bg` и `www.jarbl.bg`.
- PHP разширения: ctype, curl, dom, fileinfo, filter, hash, mbstring, openssl, PDO, pdo_mysql, session, SimpleXML, tokenizer, xml. `proc_open` за планировчика; `pcntl` позволява точни timeout-и на CLI worker-ите.
- Изходящ HTTPS достъп до API доставчиците. Пълният първоначален JAR каталог може да изисква до 15 минути за една задача; лимитът на вашия план трябва да го позволява. При по-кратък лимит е необходима отделна worker среда или допълнително разделяне на каталога на малки задачи.

SuperHosting посочва PHP 8.4 в [PHP Manager](https://help.superhosting.bg/php5-linux-hosting.html). В актуалната [политика за споделен хостинг, чл. 28](https://www.superhosting.bg/terms/Acceptable-Use-Policy-BG.pdf) Cron интервалът е минимум 15 минути. Профилът в този пакет е съобразен с това; не поставяйте едноминутните локални настройки на споделения план.

## 3. Разположение на файловете

Направете резервно копие на текущия сайт и базата преди замяна. Във File Manager включете **Settings → Show Hidden Files**, за да виждате `.env.example` и `.htaccess`.

Стандартното разположение е:

```text
/home/CPANEL_USER/jar-app/
/home/CPANEL_USER/public_html/
```

Качете и разархивирайте първо в отделна временна папка **извън public_html**. Поставете `jar-app` в home директорията. След настройването на приложението прехвърлете съдържанието на пакетираната `public_html` в реалната публична папка на домейна, включително скрития `.htaccess`. Не качвайте цялото приложение вътре в `public_html`.

За главен домейн cPanel обикновено не позволява промяна на document root — затова архивът вече използва разделени папки. [Официална документация](https://docs.cpanel.net/cpanel/domains/domains/manage-the-domain/)

Ако document root на `jarbl.bg` е друга папка, променете **двата пътя**:

1. `index.php` в публичната папка: `$appRoot` трябва да сочи към абсолютния частен път, например `/home/CPANEL_USER/jar-app`.
2. `jar-app/public-path.php`: върнете абсолютния път до реалната публична папка на този домейн.

Пакетираният `.htaccess` задава `DirectoryIndex index.php`, така че старият React `index.html` да не се показва вместо Laravel. Запазете резервно копие на старата `.htaccess`; ако хостингът е добавил PHP handler блок, възстановете подходящия блок чрез PHP Manager.

## 4. База, домейн и администратор

В cPanel → MySQL Databases създайте база, потребител и му задайте правата върху тази база. В `jar-app` копирайте `.env.example` като `.env` и попълнете:

- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — пълните имена с cPanel префикса. Парола със специални символи се поставя в кавички.
- Домейнът е подготвен за **https://jarbl.bg**. Ако предпочитате `jarbl.com`, променете APP_URL, ASSET_URL, PRIMARY_DOMAIN, CANONICAL_HOST и REDIRECT_HOSTS заедно.
- `ADMIN_USER_EMAIL` и силна уникална `ADMIN_USER_PASSWORD` за първоначалния реален администратор.
- Оставете `APP_ENV=production`, `APP_DEBUG=false`, secure cookies и изключен Skroutz feed.
- `TRUSTED_PROXIES` остава празно при директен Apache. При reverse proxy въведете само потвърдените адреси; не използвайте `*` без проверка, защото Skroutz webhook използва IP ограничение.

Изпълнете в cPanel Terminal/SSH, като замените `CPANEL_USER` с вашето име. Проверете PHP пътя с хостинга; показаният е стандартният EasyApache PHP 8.4 CLI път, не php-cgi.

```sh
cd /home/CPANEL_USER/jar-app
/opt/cpanel/ea-php84/root/usr/bin/php -v
/opt/cpanel/ea-php84/root/usr/bin/php artisan key:generate --force
/opt/cpanel/ea-php84/root/usr/bin/php artisan migrate --force
/opt/cpanel/ea-php84/root/usr/bin/php artisan db:seed --class=AdminUserSeeder --force
```

Тези стъпки са за **първа инсталация**. При обновяване запазете съществуващите `.env`, APP_KEY, база и `storage`; не изпълнявайте `key:generate` или AdminUserSeeder отново. Нов APP_KEY прави старите криптирани интеграционни ключове нечетими. Никога не използвайте `migrate:fresh`, общия демонстрационен seeder или локалния акаунт `admin@jar.local` на продукция.

След създаване на администратора изтрийте стойността на `ADMIN_USER_PASSWORD` от `.env`. При липса на Terminal/SSH предоставете тези CLI команди на поддръжката на хостинга. Няма публичен уеб инсталатор, който да създава администратори или да изпълнява миграции.

```sh
/opt/cpanel/ea-php84/root/usr/bin/php artisan config:cache
/opt/cpanel/ea-php84/root/usr/bin/php artisan route:cache
/opt/cpanel/ea-php84/root/usr/bin/php artisan view:cache
/opt/cpanel/ea-php84/root/usr/bin/php cpanel-preflight.php --database
```

Папките `storage/` и `bootstrap/cache/` трябва да са записваеми от PHP под потребителя на акаунта. Обичайно директории 0755 и файлове 0644 са достатъчни; за `.env` използвайте 0600 при PHP под същия потребител. Не задавайте 0777.

## 5. Трите Cron задачи

В cPanel → Cron Jobs добавете следните отделни задачи. Подменете името на акаунта във всеки път. Скриптът използва заключване на файл; при все още работещ предишен екземпляр следващият излиза, без да го дублира. Worker-ите приключват при празна опашка и имат лимит на задачи/време, а не работят постоянно.

| Minute | Hour / Day / Month / Weekday | Задача |
| --- | --- | --- |
| `0,15,30,45` | `*` във всяко поле | schedule |
| `1,16,31,46` | `*` във всяко поле | integrations |
| `2,17,32,47` | `*` във всяко поле | notifications |

Командите, съответно:

```sh
/opt/cpanel/ea-php84/root/usr/bin/php /home/CPANEL_USER/jar-app/cpanel-cron.php schedule >> /home/CPANEL_USER/jar-app/storage/logs/cpanel-schedule.log 2>&1
/opt/cpanel/ea-php84/root/usr/bin/php /home/CPANEL_USER/jar-app/cpanel-cron.php integrations >> /home/CPANEL_USER/jar-app/storage/logs/cpanel-integrations.log 2>&1
/opt/cpanel/ea-php84/root/usr/bin/php /home/CPANEL_USER/jar-app/cpanel-cron.php notifications >> /home/CPANEL_USER/jar-app/storage/logs/cpanel-notifications.log 2>&1
```

`INTEGRATION_POLLING_FLOOR_MINUTES=15` показва същия минимален интервал в панела. Входящите форми се записват веднага; имейл известията и обработката на Skroutz webhook може да изчакат следващия worker. При натрупана опашка закъснението може да е по-голямо. Проверявайте частните логове и „История на обновяванията“. Завършването на започната API задача може да надвиши общия `--max-time`; самата задача има timeout 900 секунди при работещ pcntl. Това трябва да се съобрази с лимитите на конкретния план.

Не добавяйте тези Cron worker-и едновременно с постоянни worker-и за същото приложение. След обновяване изпълнете `php artisan queue:restart` с правилната PHP версия. [cPanel Cron Jobs](https://docs.cpanel.net/cpanel/advanced/cron-jobs/) · [SuperHosting PHP CLI](https://help.superhosting.bg/cron-jobs.html)

## 6. Поща и API достъпи

`MAIL_MAILER=log` по подразбиране **не изпраща писма**. За реални известия задайте `smtp`, SMTP host/port/user/password и разрешен адрес на подателя; настройте SPF/DKIM според доставчика. За порт 587 използвайте `MAIL_SCHEME=smtp` (STARTTLS); за 465 — `smtps`, ако доставчикът го изисква. След промени: `php artisan config:cache` и `php artisan queue:restart`.

Влезте на `/admin/integrations` и свържете JAR, пощата и Google. Ключовете се записват криптирано в базата. ЕЦБ не изисква ключ. За конкурентни цени е нужен разрешен feed; търговският Skroutz API сам по себе си не предоставя всички конкурентни оферти.

Skroutz остава изключен до предоставяне на вашия Merchant token и попълнен/проверен продуктов каталог. Подготвените адреси са `https://jarbl.bg/feeds/skroutz.xml` и `https://jarbl.bg/webhooks/skroutz`; включването е описано в ADMIN-AUTOMATION.md. Не са активирани автоматично приемане на поръчки или изпращане на цени.

## 7. Проверка след качване

1. `/up`, начална страница, `/login` и `/admin` се зареждат през HTTPS; favicon е JAR.
2. Неавтентикиран `/admin/workspace/data` връща отказ/вход, а `.env` и частната папка не са достъпни през браузър.
3. Реалният администратор влиза; всички пет раздела, шаблоните и Skroutz подготовката работят.
4. Контролна заявка от формата се появява като заявка, клиент, поръчка и разговор; потвърдете реалното получаване на имейл след активиране на SMTP.
5. След Cron цикъл `/admin/integrations` показва нов heartbeat и успешни курсове от ЕЦБ.
6. Новата конфигурация остава след обновяване на страницата; непълна/непрегледана конфигурация не влиза в Skroutz XML.

Архивът е проверен локално за цялост, зависимости, asset manifest-и, маршрути и шаблони. Свързването към вашия MySQL, SMTP, хостинг и частни API се проверява след попълване на реалните данни. Сайтът `jarbl.bg` не е променян при подготовката на пакета.

За повторно изграждане от работната папка в Windows: `build-cpanel.cmd`. Получавате нов ZIP и SHA-256 файл в `dist/`.
