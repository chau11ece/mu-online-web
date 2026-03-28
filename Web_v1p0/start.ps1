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

# Verify PHP binary exists before trying to start it
Write-Host "Checking PHP binary at C:\php\php.exe ..."
if (-not (Test-Path "C:\php\php.exe")) {
    Write-Error "C:\php\php.exe not found - PHP directory was not copied into the image!"
    exit 1
}
Write-Host "PHP binary found. Version:"
& "C:\php\php.exe" --version

Write-Host "Starting PHP built-in server on 0.0.0.0:80..."

# Run PHP in the foreground — keeps the container alive until PHP exits.
& "C:\php\php.exe" -c "C:\php\php.ini" -S "0.0.0.0:80" -t "C:\inetpub\wwwroot"

# If PHP exits, log the exit code and propagate it so Docker knows the container failed.
$code = $LASTEXITCODE
Write-Host "PHP server exited with code: $code"
exit $code
