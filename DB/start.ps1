$ErrorActionPreference = 'Stop'

# Clean up any existing error log files to prevent locking issues
$logPath = 'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\Log\ERRORLOG'
if (Test-Path $logPath) {
    Remove-Item $logPath -Force
}

# Start SQL Server process in the background
$process = Start-Process "C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\Binn\sqlservr.exe" -PassThru

# Wait for SQL Server to start
Start-Sleep 30

# Check if the main DB is missing and restore if needed
if (-not (Test-Path C:\SQLData\MuOnline.mdf)) {
    & C:/restore-databases.ps1
}

# Keep the container running by waiting for the SQL Server process
$process.WaitForExit()
