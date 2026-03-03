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

Write-Host "Starting PHP built-in server on 0.0.0.0:80..."

# Run PHP in the foreground — this keeps the container alive.
# The script (and therefore the container) exits only if PHP itself exits.
& "C:\php\php.exe" -c "C:\php\php.ini" -S "0.0.0.0:80" -t "C:\inetpub\wwwroot"
