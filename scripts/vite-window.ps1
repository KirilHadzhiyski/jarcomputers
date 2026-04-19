param(
    [string]$ServerHost = '127.0.0.1',
    [int]$Port = 5173
)

. (Join-Path $PSScriptRoot 'LocalEnv.ps1')

Initialize-LocalEnvironment
Ensure-NodeDependencies

Write-Host "Vite dev server: http://$ServerHost`:$Port" -ForegroundColor Cyan
& (Get-NpmExe) run dev -- --host $ServerHost --port $Port
