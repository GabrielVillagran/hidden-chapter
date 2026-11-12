param([string[]]$Component)
$ErrorActionPreference = 'Stop'
$theme = Join-Path $PSScriptRoot 'wp-content/themes/ellamara'
$plugins = Get-ChildItem (Join-Path $PSScriptRoot 'wp-content/plugins') -Directory
$components = @(Get-Item $theme) + @($plugins)
if ($Component) {
    foreach ($name in $Component) {
        if ($name -notin $components.Name) { throw "Componente desconocido: $name" }
    }
    $components = $components | Where-Object Name -In $Component
}
$dist = Join-Path $PSScriptRoot 'dist'
New-Item -ItemType Directory $dist -Force | Out-Null
foreach ($folder in $components) {
    Compress-Archive -LiteralPath $folder.FullName -DestinationPath (Join-Path $dist "$($folder.Name).zip") -Force
}
