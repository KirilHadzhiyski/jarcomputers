param()
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'scripts\LocalEnv.ps1')
Initialize-LocalEnvironment
Ensure-ComposerDependencies
Ensure-NodeDependencies
& npm run build
if ($LASTEXITCODE -ne 0) { throw 'Frontend build failed.' }
& python (Join-Path $PSScriptRoot 'tools\build-cpanel.py') --php (Get-PhpExe) --composer (Join-Path $PSScriptRoot 'tools\composer\composer.phar')
if ($LASTEXITCODE -ne 0) { throw 'cPanel packaging failed.' }
