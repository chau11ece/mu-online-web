$ErrorActionPreference = 'Stop'

# Override SQLServerName in IGCDS.ini when SQL_SERVER env var is provided.
# Used by docker-compose.local.yml to point at the Windows host SQL instance
# instead of the SQL container.
if ($env:SQL_SERVER) {
    Write-Host "Patching IGCDS.ini: SQLServerName = $($env:SQL_SERVER)"
    $ini = Get-Content "C:\DataServer\IGCDS.ini"
    $ini = $ini | ForEach-Object {
        if ($_ -match '^SQLServerName') {
            "SQLServerName`t`t`t`t= $($env:SQL_SERVER)"
        } else {
            $_
        }
    }
    Set-Content "C:\DataServer\IGCDS.ini" $ini
    Write-Host "IGCDS.ini patched successfully."
}

Write-Host "Starting IGC.DataServer.exe..."
& "C:\DataServer\IGC.DataServer.exe"
