[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string] $OutputDirectory,
    [string] $HostName = $env:DB_HOST,
    [int] $Port = 3306,
    [string] $Database = $env:DB_DATABASE,
    [string] $User = $env:DB_USERNAME
)

$ErrorActionPreference = 'Stop'

if (-not [string]::IsNullOrWhiteSpace($env:DB_PORT)) {
    $Port = [int] $env:DB_PORT
}

if ([string]::IsNullOrWhiteSpace($HostName) -or [string]::IsNullOrWhiteSpace($Database) -or [string]::IsNullOrWhiteSpace($User)) {
    throw 'DB_HOST, DB_DATABASE, dan DB_USERNAME wajib tersedia melalui environment atau parameter.'
}
if (-not (Get-Command mysqldump -ErrorAction SilentlyContinue)) {
    throw 'mysqldump tidak ditemukan. Pasang MySQL client pada host backup.'
}
if ([string]::IsNullOrWhiteSpace($env:MYSQL_PWD)) {
    throw 'MYSQL_PWD harus disediakan oleh secret manager/session aman; password tidak diterima sebagai argumen script.'
}

New-Item -ItemType Directory -Force -Path $OutputDirectory | Out-Null
$stamp = Get-Date -Format 'yyyyMMdd-HHmmss'
$target = Join-Path (Resolve-Path $OutputDirectory) "$Database-$stamp.sql"

mysqldump --host=$HostName --port=$Port --user=$User --single-transaction --routines --events --triggers --set-gtid-purged=OFF --result-file=$target $Database
if ($LASTEXITCODE -ne 0) {
    Remove-Item -LiteralPath $target -Force -ErrorAction SilentlyContinue
    throw "mysqldump gagal dengan exit code $LASTEXITCODE."
}

Write-Output "Backup dibuat: $target"
