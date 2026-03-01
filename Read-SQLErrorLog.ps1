# Read SQL Server Error Log
# Run as Administrator

Write-Host "=== SQL Server Error Log Reader ===" -ForegroundColor Cyan
Write-Host ""

$logDir = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log"

if (-not (Test-Path $logDir)) {
    Write-Host "[ERROR] Log directory not found: $logDir" -ForegroundColor Red
    exit 1
}

Write-Host "Reading error log from: $logDir" -ForegroundColor Yellow
Write-Host ""

# Get the most recent ERRORLOG file
$logFiles = Get-ChildItem -Path $logDir -Filter "ERRORLOG*" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending

if ($logFiles) {
    $latestLog = $logFiles[0].FullName
    Write-Host "Latest log file: $latestLog" -ForegroundColor Green
    Write-Host "Last modified: $($logFiles[0].LastWriteTime)" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "=== Last 30 lines of error log ===" -ForegroundColor Yellow
    Write-Host ""
    
    try {
        $content = Get-Content $latestLog -Tail 30 -ErrorAction Stop
        foreach ($line in $content) {
            if ($line -match "Error|error|ERROR|Failed|failed|FAILED|Cannot|cannot|CANNOT") {
                Write-Host $line -ForegroundColor Red
            } elseif ($line -match "Warning|warning|WARNING") {
                Write-Host $line -ForegroundColor Yellow
            } else {
                Write-Host $line -ForegroundColor White
            }
        }
    } catch {
        Write-Host "[ERROR] Could not read log file: $_" -ForegroundColor Red
        Write-Host "You may need to run PowerShell as Administrator" -ForegroundColor Yellow
    }
} else {
    Write-Host "[WARNING] No ERRORLOG files found!" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== End of Error Log ===" -ForegroundColor Cyan
