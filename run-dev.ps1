param(
    [string]$ServerHost = '127.0.0.1',
    [int]$Port = 8000,
    [int]$VitePort = 5173
)

. (Join-Path $PSScriptRoot 'scripts\LocalEnv.ps1')

Initialize-LocalEnvironment
Ensure-ComposerDependencies
Ensure-NodeDependencies
Ensure-AppKey
& (Get-PhpExe) artisan migrate --ansi --force

$viteScript = Join-Path $PSScriptRoot 'scripts\vite-window.ps1'
Start-Process powershell.exe -WorkingDirectory (Get-ProjectRoot) -ArgumentList @(
    '-NoExit',
    '-ExecutionPolicy', 'Bypass',
    '-File', $viteScript,
    '-ServerHost', $ServerHost,
    '-Port', $VitePort
)

Write-Host "Laravel app: http://$ServerHost`:$Port" -ForegroundColor Green
Write-Host "Vite dev server: http://$ServerHost`:$VitePort" -ForegroundColor Cyan
Write-Host 'A second PowerShell window was opened for Vite.' -ForegroundColor Yellow
& (Get-PhpExe) artisan serve --host $ServerHost --port $Port
