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
    ".agents",
    ".codex"
)

Get-ChildItem -LiteralPath $moduleRoot -Force | Where-Object {
    $exclude -notcontains $_.Name
} | ForEach-Object {
    Copy-Item -LiteralPath $_.FullName -Destination $packageRoot -Recurse -Force
}

if (Test-Path $outputPath) {
    Remove-Item -LiteralPath $outputPath -Force
}

Compress-Archive -Path $packageRoot -DestinationPath $outputPath -Force

Write-Host "Package created: $outputPath"
