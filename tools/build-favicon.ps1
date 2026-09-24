$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Drawing
$projectPath = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$sourcePath = Join-Path $projectPath 'public\images\branding\jar-computers-logo-blue.png'
$sourceImage = [System.Drawing.Image]::FromFile($sourcePath)
$pngData = [Convert]::ToBase64String([IO.File]::ReadAllBytes($sourcePath))
$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><title>JAR Computers Благоевград</title><rect width="1024" height="1024" rx="120" fill="white"/><image x="-60" y="150" width="4000" height="800" href="data:image/png;base64,' + $pngData + '"/></svg>'
[IO.File]::WriteAllText((Join-Path $projectPath 'public\favicon.svg'), $svg, [Text.UTF8Encoding]::new($false))
$iconImages = @()
try {
    foreach ($size in @(16, 32, 48, 64, 128, 180, 256)) {
        $bitmap = [System.Drawing.Bitmap]::new($size, $size)
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        $stream = [IO.MemoryStream]::new()
        try {
            $graphics.Clear([System.Drawing.Color]::White)
            $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $scale = $size / 1024.0
            $rectangle = [System.Drawing.RectangleF]::new([single](-60 * $scale), [single](150 * $scale), [single](4000 * $scale), [single](800 * $scale))
            $graphics.DrawImage($sourceImage, $rectangle)
            $bitmap.Save($stream, [System.Drawing.Imaging.ImageFormat]::Png)
            $bytes = $stream.ToArray()
            if ($size -eq 32) { [IO.File]::WriteAllBytes((Join-Path $projectPath 'public\favicon-32.png'), $bytes) }
            if ($size -eq 180) { [IO.File]::WriteAllBytes((Join-Path $projectPath 'public\apple-touch-icon.png'), $bytes) }
            else { $iconImages += [pscustomobject]@{ Size = $size; Bytes = $bytes } }
        } finally { $stream.Dispose(); $graphics.Dispose(); $bitmap.Dispose() }
    }
    $iconStream = [IO.MemoryStream]::new()
    $writer = [IO.BinaryWriter]::new($iconStream)
    try {
        $writer.Write([uint16]0); $writer.Write([uint16]1); $writer.Write([uint16]$iconImages.Count)
        $offset = 6 + 16 * $iconImages.Count
        foreach ($entry in $iconImages) {
            $dimension = if ($entry.Size -eq 256) { 0 } else { $entry.Size }
            $writer.Write([byte]$dimension); $writer.Write([byte]$dimension)
            $writer.Write([byte]0); $writer.Write([byte]0); $writer.Write([uint16]1); $writer.Write([uint16]32)
            $writer.Write([uint32]$entry.Bytes.Length); $writer.Write([uint32]$offset)
            $offset += $entry.Bytes.Length
        }
        foreach ($entry in $iconImages) { $writer.Write([byte[]]$entry.Bytes) }
        [IO.File]::WriteAllBytes((Join-Path $projectPath 'public\favicon.ico'), $iconStream.ToArray())
    } finally { $writer.Dispose(); $iconStream.Dispose() }
    foreach ($file in @('favicon.svg', 'favicon.ico', 'favicon-32.png', 'apple-touch-icon.png')) {
        Copy-Item -LiteralPath (Join-Path $projectPath ('public\' + $file)) -Destination (Join-Path $projectPath ('legacy-react\public\' + $file))
    }
} finally { $sourceImage.Dispose() }
Write-Output 'Brand favicons exported for Laravel and legacy React.'
