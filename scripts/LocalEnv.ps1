Set-StrictMode -Version Latest

$script:ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path

function Find-WingetPackageExecutable {
    param(
        [string[]]$PackagePrefixes,
        [string]$RelativePath
    )

    $packagesRoot = Join-Path $env:LOCALAPPDATA 'Microsoft\WinGet\Packages'

    if (-not (Test-Path -LiteralPath $packagesRoot)) {
        return $null
    }

    foreach ($prefix in $PackagePrefixes) {
        $package = Get-ChildItem -LiteralPath $packagesRoot -Directory -Filter "$prefix*" |
            Sort-Object Name -Descending |
            Select-Object -First 1

        if ($null -eq $package) {
            continue
        }

        $candidate = Join-Path $package.FullName $RelativePath

        if (Test-Path -LiteralPath $candidate) {
            return $candidate
        }
    }

    return $null
}

function Resolve-ToolOrCommand {
    param(
        [string]$BundledPath,
        [string]$CommandName,
        [string]$MissingMessage
    )

    if (Test-Path -LiteralPath $BundledPath) {
        return $BundledPath
    }

    $command = Get-Command $CommandName -ErrorAction SilentlyContinue

    if ($command) {
        return $command.Source
    }

    throw $MissingMessage
}

function Get-ProjectRoot {
    return $script:ProjectRoot
}

function Enter-ProjectRoot {
    Set-Location (Get-ProjectRoot)
}

function Get-PhpExe {
    $bundled = Join-Path (Get-ProjectRoot) 'tools\php\php.exe'

    if (Test-Path -LiteralPath $bundled) {
        return $bundled
    }

    $command = Get-Command 'php.exe' -ErrorAction SilentlyContinue

    if ($command) {
        return $command.Source
    }

    $wingetPhp = Find-WingetPackageExecutable `
        -PackagePrefixes @('PHP.PHP.8.5', 'PHP.PHP.8.4', 'PHP.PHP.8.3', 'PHP.PHP.8.2') `
        -RelativePath 'php.exe'

    if ($wingetPhp) {
        return $wingetPhp
    }

    throw 'PHP runtime not found. Install PHP or restore tools\php.'
}

function Get-ComposerPhar {
    return Resolve-ToolOrCommand `
        -BundledPath (Join-Path (Get-ProjectRoot) 'tools\composer\composer.phar') `
        -CommandName 'composer' `
        -MissingMessage 'Composer not found. Install Composer or restore tools\composer\composer.phar.'
}

function Get-NodeDir {
    $nodeRoot = Join-Path (Get-ProjectRoot) 'tools\node'
    if (-not (Test-Path -LiteralPath $nodeRoot)) {
        foreach ($candidate in @(
            (Join-Path $env:ProgramFiles 'nodejs'),
            (Join-Path ${env:ProgramFiles(x86)} 'nodejs')
        )) {
            if ($candidate -and (Test-Path -LiteralPath (Join-Path $candidate 'npm.cmd'))) {
                return $candidate
            }
        }

        return $null
    }

    $directory = Get-ChildItem -LiteralPath $nodeRoot -Directory | Sort-Object Name -Descending | Select-Object -First 1

    if ($null -eq $directory) {
        return $null
    }

    return $directory.FullName
}

function Get-NpmExe {
    $nodeDir = Get-NodeDir

    if ($nodeDir) {
        $bundled = Join-Path $nodeDir 'npm.cmd'

        if (Test-Path -LiteralPath $bundled) {
            return $bundled
        }
    }

    $command = Get-Command 'npm.cmd' -ErrorAction SilentlyContinue

    if ($command) {
        return $command.Source
    }

    throw 'npm not found. Install Node.js or restore tools\node.'
}

function Get-NpxExe {
    $nodeDir = Get-NodeDir

    if ($nodeDir) {
        $bundled = Join-Path $nodeDir 'npx.cmd'

        if (Test-Path -LiteralPath $bundled) {
            return $bundled
        }
    }

    $command = Get-Command 'npx.cmd' -ErrorAction SilentlyContinue

    if ($command) {
        return $command.Source
    }

    throw 'npx not found. Install Node.js or restore tools\node.'
}

function Get-GitExe {
    foreach ($candidate in @(
        (Join-Path $env:ProgramFiles 'Git\cmd\git.exe'),
        (Join-Path ${env:ProgramFiles(x86)} 'Git\cmd\git.exe')
    )) {
        if ($candidate -and (Test-Path -LiteralPath $candidate)) {
            return $candidate
        }
    }

    $command = Get-Command 'git.exe' -ErrorAction SilentlyContinue

    if ($command) {
        return $command.Source
    }

    return $null
}

function Initialize-LocalEnvironment {
    Enter-ProjectRoot

    foreach ($directory in @('.composer', '.composer-cache', '.npm-cache', 'database')) {
        if (-not (Test-Path -LiteralPath $directory)) {
            New-Item -ItemType Directory -Path $directory -Force | Out-Null
        }
    }

    if (-not (Test-Path -LiteralPath '.env')) {
        Copy-Item -LiteralPath '.env.example' -Destination '.env'
    }

    if (-not (Test-Path -LiteralPath 'database\database.sqlite')) {
        New-Item -ItemType File -Path 'database\database.sqlite' -Force | Out-Null
    }

    $phpParent = Split-Path (Get-PhpExe) -Parent
    $nodeDir = Get-NodeDir
    $gitExe = Get-GitExe

    $pathEntries = @($phpParent)

    if ($nodeDir) {
        $pathEntries += $nodeDir
    }

    if ($gitExe) {
        $pathEntries += (Split-Path $gitExe -Parent)
    }

    $pathPrefix = ($pathEntries | Where-Object { $_ } | Select-Object -Unique) -join ';'

    if ($pathPrefix) {
        $env:PATH = "$pathPrefix;$env:PATH"
    }

    $env:COMPOSER_HOME = (Resolve-Path '.composer').Path
    $env:COMPOSER_CACHE_DIR = (Resolve-Path '.composer-cache').Path
    $env:NPM_CONFIG_CACHE = (Resolve-Path '.npm-cache').Path
}

function Ensure-AppKey {
    $envFile = Join-Path (Get-ProjectRoot) '.env'
    $appKeyLine = Select-String -Path $envFile -Pattern '^APP_KEY=' | Select-Object -First 1

    if (-not $appKeyLine -or $appKeyLine.Line -eq 'APP_KEY=') {
        & (Get-PhpExe) artisan key:generate --ansi --force
    }
}

function Ensure-ComposerDependencies {
    if (-not (Test-Path -LiteralPath 'vendor\autoload.php')) {
        & (Get-PhpExe) (Get-ComposerPhar) install --no-interaction --no-progress
    }
}

function Ensure-NodeDependencies {
    if (-not (Test-Path -LiteralPath 'node_modules')) {
        & (Get-NpmExe) install --no-fund --no-audit
    }
}

function Ensure-BuildAssets {
    if (-not (Test-Path -LiteralPath 'public\build\manifest.json')) {
        & (Get-NpmExe) run build
    }
}
