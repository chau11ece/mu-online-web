$ErrorActionPreference = 'Stop'

# Override $sql_host in config.php when SQL_SERVER env var is provided.
# Used by docker-compose.local.yml to point at the Windows host SQL instance.
if ($env:SQL_SERVER) {
    Write-Host "Patching config.php: sql_host = $($env:SQL_SERVER)"
    $cfg = Get-Content "C:\inetpub\wwwroot\configs\config.php"
    $cfg = $cfg | ForEach-Object {
        if ($_ -match '^\$sql_host\s*=') {
            "`$sql_host = '$($env:SQL_SERVER)';        // SQL Server (overridden by SQL_SERVER env)"
        } else {
            $_
        }
    }
    Set-Content "C:\inetpub\wwwroot\configs\config.php" $cfg
    Write-Host "config.php patched successfully."
}

Write-Host "Starting PHP server..."

Start-Process "C:\php\php.exe" -ArgumentList "-c", "C:\php\php.ini", "-S", "0.0.0.0:80", "-t", "C:\inetpub\wwwroot" -NoNewWindow

Write-Host "PHP server started. Keeping container running..."

while ($true) {
    Start-Sleep 60
    # Optional: Check if PHP is still running
    $phpProcess = Get-Process php -ErrorAction SilentlyContinue
    if (-not $phpProcess) {
        Write-Error "PHP server stopped unexpectedly. Exiting."
        exit 1
    }
}
