param([switch]$Stop)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'scripts\LocalEnv.ps1')
Initialize-LocalEnvironment
$phpExecutable = Get-PhpExe
$artisanFile = Join-Path $PSScriptRoot 'artisan'
$logDirectory = Join-Path $PSScriptRoot 'storage\logs'
New-Item -ItemType Directory -Path $logDirectory -Force | Out-Null
$workers = @{
    worker = 'queue:work integrations --queue=integrations --sleep=2 --tries=1 --timeout=900 --no-ansi'
    notifications = 'queue:work database --queue=default --sleep=2 --tries=3 --timeout=60 --no-ansi'
    scheduler = 'schedule:work --no-ansi'
}
foreach ($workerName in $workers.Keys) {
    $pidPath = Join-Path $PSScriptRoot ('storage\automation-' + $workerName + '.pid')
    $existingProcess = $null
    if (Test-Path -LiteralPath $pidPath) {
        $workerPid = 0
        if ([int]::TryParse((Get-Content -LiteralPath $pidPath -Raw).Trim(), [ref]$workerPid)) {
            $candidate = Get-CimInstance Win32_Process -Filter "ProcessId = $workerPid" -ErrorAction SilentlyContinue
            if ($candidate -and $candidate.CommandLine -and $candidate.CommandLine.Contains($artisanFile) -and $candidate.CommandLine.Contains($workers[$workerName])) {
                $existingProcess = $candidate
            }
        }
    }
    if ($Stop) {
        if ($existingProcess) { Stop-Process -Id $existingProcess.ProcessId; Write-Output "$workerName stopped." }
        if (Test-Path -LiteralPath $pidPath) { Remove-Item -LiteralPath $pidPath }
        continue
    }
    if ($existingProcess) { Write-Output "$workerName is already running."; continue }
    $arguments = '"' + $artisanFile + '" ' + $workers[$workerName]
    $process = Start-Process -FilePath $phpExecutable -ArgumentList $arguments -WorkingDirectory $PSScriptRoot -WindowStyle Hidden -PassThru `
        -RedirectStandardOutput (Join-Path $logDirectory ('automation-' + $workerName + '.log')) `
        -RedirectStandardError (Join-Path $logDirectory ('automation-' + $workerName + '-error.log'))
    Set-Content -LiteralPath $pidPath -Value $process.Id
    Write-Output "$workerName started (PID $($process.Id))."
}
if (-not $Stop) { & $phpExecutable $artisanFile integrations:dispatch --no-ansi }
