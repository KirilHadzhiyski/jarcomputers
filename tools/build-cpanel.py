"""Build a self-contained, split-root cPanel release without local data or secrets."""
from __future__ import annotations

import argparse
import calendar
import hashlib
import json
import os
from datetime import datetime, timedelta, timezone
from pathlib import Path
import shutil
import subprocess
import zipfile


def sofia_now() -> datetime:
    """Return current wall-clock time in Sofia without an external tzdata package."""
    utc_now = datetime.now(timezone.utc)

    def last_sunday(month: int) -> datetime:
        last_day = calendar.monthrange(utc_now.year, month)[1]
        candidate = datetime(utc_now.year, month, last_day, tzinfo=timezone.utc)
        return candidate - timedelta(days=(candidate.weekday() + 1) % 7)

    summer_time_starts = last_sunday(3).replace(hour=1)
    summer_time_ends = last_sunday(10).replace(hour=1)
    offset_hours = 3 if summer_time_starts <= utc_now < summer_time_ends else 2
    offset = timezone(timedelta(hours=offset_hours))

    return utc_now.astimezone(offset)


def main() -> None:
    args = argparse.ArgumentParser()
    args.add_argument('--php', required=True)
    args.add_argument('--composer', required=True)
    options = args.parse_args()
    root = Path(__file__).resolve().parents[1]
    release_time = sofia_now()
    stamp = release_time.strftime('%Y%m%d-%H%M%S')
    dist = root / 'dist'
    stage = dist / f'cpanel-stage-{stamp}'
    app = stage / 'jar-app'
    web = stage / 'public_html'
    if stage.exists():
        raise RuntimeError('Release directory already exists; run again with a new timestamp.')
    app.mkdir(parents=True)

    # Explicit inputs only. Never copy .env, SQLite, storage contents or a local vendor tree.
    for folder in ['app', 'config', 'routes', 'resources/views', 'database/migrations', 'database/seeders', 'database/factories']:
        shutil.copytree(root / folder, app / folder)
    for filename in ['artisan', 'composer.json', 'composer.lock', 'bootstrap/app.php', 'bootstrap/providers.php']:
        target = app / filename
        target.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(root / filename, target)
    for folder in ['bootstrap/cache', 'storage/app/private', 'storage/app/public', 'storage/logs', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views']:
        (app / folder).mkdir(parents=True, exist_ok=True)
    shutil.copytree(root / 'public', web, ignore=shutil.ignore_patterns('hot', 'storage'))
    for source, target in [
        ('index.php', web / 'index.php'), ('public-path.php', app / 'public-path.php'),
        ('cron.php', app / 'cpanel-cron.php'), ('preflight.php', app / 'cpanel-preflight.php'),
        ('.env.example', app / '.env.example'),
    ]:
        shutil.copy2(root / 'deploy/cpanel' / source, target)
    (app / '.htaccess').write_text('Require all denied\n', encoding='utf-8')
    htaccess = web / '.htaccess'
    htaccess.write_text('DirectoryIndex index.php\n' + htaccess.read_text(encoding='utf-8'), encoding='utf-8')

    # Set the public path before Laravel loads configuration, for both web and Artisan.
    bootstrap = app / 'bootstrap/app.php'
    contents = bootstrap.read_text(encoding='utf-8')
    if contents.count('return Application::configure(') != 1:
        raise RuntimeError('bootstrap/app.php changed; review the split-root adaptation.')
    contents = contents.replace('return Application::configure(', '$app = Application::configure(', 1)
    contents += "\n$app->usePublicPath(require __DIR__.'/../public-path.php');\n\nreturn $app;\n"
    bootstrap.write_text(contents, encoding='utf-8')

    build_env = os.environ.copy()
    build_env['JAR_PUBLIC_PATH'] = str(web.resolve())
    subprocess.run([options.php, options.composer, 'install', '--no-dev', '--prefer-dist', '--no-interaction', '--no-progress', '--optimize-autoloader'], cwd=app, check=True, env=build_env)
    subprocess.run([options.php, 'cpanel-preflight.php', '--build-check'], cwd=app, check=True, env=build_env)
    subprocess.run([options.php, 'artisan', 'route:list', '--path=admin', '--json'], cwd=app, check=True, stdout=subprocess.DEVNULL, env=build_env)
    subprocess.run([options.php, 'artisan', 'view:cache', '--no-ansi'], cwd=app, check=True, env=build_env)
    subprocess.run([options.php, 'artisan', 'view:clear', '--no-ansi'], cwd=app, check=True, env=build_env)

    shutil.copy2(root / 'docs/cpanel-upload.md', stage / 'UPLOAD-CPANEL.md')
    shutil.copy2(root / 'docs/admin-automation.md', stage / 'ADMIN-AUTOMATION.md')
    (stage / 'RELEASE.json').write_text(json.dumps({
        'built_at_utc': datetime.now(timezone.utc).isoformat(), 'php_minimum': '8.4',
        'domain_example': 'jarbl.bg', 'layout': ['jar-app (private)', 'public_html (web root)'],
        'document_root': '/home/CPANEL_USER/public_html/jarbl.bg',
        'composer_dev_dependencies': False, 'contains_credentials_or_database': False,
        'cron_interval_minutes': 15,
    }, indent=2), encoding='utf-8')

    files = sorted(p for p in stage.rglob('*') if p.is_file())
    for file in files:
        rel = file.relative_to(stage).as_posix()
        if file.name in {'.env', 'auth.json', 'config.php'} and (file.name != 'config.php' or rel == 'jar-app/bootstrap/cache/config.php'):
            raise RuntimeError(f'Unsafe release file: {rel}')
        if file.suffix in {'.sqlite', '.log', '.pid'} or 'node_modules' in file.parts:
            raise RuntimeError(f'Local runtime artifact in release: {rel}')
    installed = json.loads((app / 'vendor/composer/installed.json').read_text(encoding='utf-8'))
    if installed.get('dev') is not False or any(p['name'] in {'phpunit/phpunit', 'laravel/pint', 'fakerphp/faker', 'laravel/pail'} for p in installed['packages']):
        raise RuntimeError('Development dependencies found in release.')
    for build, entry in [('build', 'resources/js/app.js'), ('admin-build', 'src/admin/main.tsx')]:
        manifest = json.loads((web / build / 'manifest.json').read_text(encoding='utf-8'))
        if entry not in manifest:
            raise RuntimeError(f'Missing build entry: {entry}')
        for asset in manifest.values():
            for relative in [asset['file'], *asset.get('css', []), *asset.get('assets', [])]:
                if not (web / build / relative).is_file():
                    raise RuntimeError(f'Missing compiled asset: {relative}')

    # Unix modes allow cPanel's extractor to preserve usable directory/file permissions.
    # ZipInfo defaults to 1980-01-01 when no timestamp is supplied. Some cPanel
    # File Manager versions display that sentinel as 1899 after extraction, so
    # stamp every archive entry with the actual local release build time.
    archive_timestamp = release_time.timetuple()[:6]
    archive = dist / f'jar-computers-cpanel-{stamp}.zip'
    with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED, compresslevel=6) as output:
        for directory in sorted(p for p in stage.rglob('*') if p.is_dir()):
            entry = zipfile.ZipInfo(directory.relative_to(stage).as_posix() + '/')
            entry.date_time = archive_timestamp
            entry.create_system = 3
            entry.external_attr = (0o40755 << 16) | 0x10
            output.writestr(entry, b'')
        for file in files:
            entry = zipfile.ZipInfo(file.relative_to(stage).as_posix())
            entry.date_time = archive_timestamp
            entry.create_system = 3
            entry.external_attr = 0o100644 << 16
            entry.compress_type = zipfile.ZIP_DEFLATED
            output.writestr(entry, file.read_bytes())
    with zipfile.ZipFile(archive) as release:
        if release.testzip() is not None:
            raise RuntimeError('Archive integrity check failed.')
        unexpected_timestamps = [
            entry.filename for entry in release.infolist()
            if entry.date_time[:5] != archive_timestamp[:5]
        ]
        if unexpected_timestamps:
            raise RuntimeError(
                f'Archive timestamp check failed for {len(unexpected_timestamps)} entries.'
            )
    with archive.open('rb') as archive_stream:
        digest = hashlib.file_digest(archive_stream, 'sha256').hexdigest()
    archive.with_suffix('.sha256').write_text(f'{digest}  {archive.name}\n', encoding='ascii')
    print(json.dumps({'archive': str(archive), 'stage': str(stage), 'files': len(files), 'size_bytes': archive.stat().st_size, 'sha256': digest}, indent=2))


if __name__ == '__main__':
    main()
