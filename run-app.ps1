param(
    [string]$ServerHost = '127.0.0.1',
    [int]$Port = 8000,
    [switch]$SkipMigrate
)

. (Join-Path $PSScriptRoot 'scripts\LocalEnv.ps1')

Initialize-LocalEnvironment
Ensure-ComposerDependencies
Ensure-NodeDependencies
Ensure-AppKey
Ensure-BuildAssets

if (-not $SkipMigrate) {
    & (Get-PhpExe) artisan migrate --ansi --force
}

Write-Host "App running at http://$ServerHost`:$Port" -ForegroundColor Green
& (Get-PhpExe) artisan serve --host $ServerHost --port $Port
