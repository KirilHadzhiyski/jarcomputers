param(
    [switch]$SkipBuild
)

. (Join-Path $PSScriptRoot 'scripts\LocalEnv.ps1')

Initialize-LocalEnvironment

Write-Host 'Installing PHP dependencies...' -ForegroundColor Cyan
& (Get-PhpExe) (Get-ComposerPhar) install --no-interaction --no-progress

Write-Host 'Installing Node dependencies...' -ForegroundColor Cyan
& (Get-NpmExe) install --no-fund --no-audit

Write-Host 'Preparing Laravel environment...' -ForegroundColor Cyan
Ensure-AppKey
& (Get-PhpExe) artisan migrate --ansi --force

if (-not $SkipBuild) {
    Write-Host 'Building frontend assets...' -ForegroundColor Cyan
    & (Get-NpmExe) run build
}

Write-Host ''
Write-Host 'Setup complete.' -ForegroundColor Green
Write-Host 'Quick run: .\run-app.cmd'
Write-Host 'Dev mode:  .\run-dev.cmd'
