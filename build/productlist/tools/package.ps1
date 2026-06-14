param(
    [string] $Output = "build\productlist.zip"
)

$ErrorActionPreference = "Stop"

$moduleRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$buildRoot = Join-Path $moduleRoot "build"
$packageRoot = Join-Path $buildRoot "productlist"
$outputPath = Join-Path $moduleRoot $Output
$resolvedBuildRoot = [System.IO.Path]::GetFullPath($buildRoot)
$resolvedPackageRoot = [System.IO.Path]::GetFullPath($packageRoot)

if (-not $resolvedPackageRoot.StartsWith($resolvedBuildRoot, [System.StringComparison]::OrdinalIgnoreCase)) {
    throw "Refusing to package outside build directory: $resolvedPackageRoot"
}

if (Test-Path $packageRoot) {
    Remove-Item -LiteralPath $packageRoot -Recurse -Force
}

New-Item -ItemType Directory -Force -Path $packageRoot | Out-Null

$exclude = @(
    "build",
    ".git",
    ".github",
    ".agents",
    ".codex",
    ".editorconfig",
    ".gitignore"
)

Get-ChildItem -LiteralPath $moduleRoot -Force | Where-Object {
    $exclude -notcontains $_.Name
} | ForEach-Object {
    Copy-Item -LiteralPath $_.FullName -Destination $packageRoot -Recurse -Force
}

if (Test-Path $outputPath) {
    Remove-Item -LiteralPath $outputPath -Force
}

# Note: Compress-Archive on Windows PowerShell 5.1 writes ZIP entries using
# backslash separators, which produces a non-compliant archive. PrestaShop
# servers (Linux) then extract it as flat files named "productlist\views\..."
# instead of nested folders, so the module fails to install. Build the archive
# manually with forward-slash entry names to stay spec-compliant and portable.
Add-Type -AssemblyName System.IO.Compression | Out-Null
Add-Type -AssemblyName System.IO.Compression.FileSystem | Out-Null

$entryBaseLength = ([System.IO.Path]::GetFullPath($buildRoot)).TrimEnd('\', '/').Length + 1
$zip = [System.IO.Compression.ZipFile]::Open($outputPath, [System.IO.Compression.ZipArchiveMode]::Create)

try {
    Get-ChildItem -LiteralPath $packageRoot -Recurse -File -Force | ForEach-Object {
        $relativePath = $_.FullName.Substring($entryBaseLength)
        $entryName = $relativePath -replace '\\', '/'
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $_.FullName, $entryName) | Out-Null
    }
}
finally {
    $zip.Dispose()
}

Write-Host "Package created: $outputPath"
