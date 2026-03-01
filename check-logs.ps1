param (
    [string]$service = "all"  # e.g., 'mu-ds' or 'all'
)

# Services with their log paths on the HOST (not in containers)
# Logs are accessible directly via bind mounts
$services = @{
    "mu-sqlserver" = @{ hostPath = "C:\MU-Project\SQL_LOG" }
    "mu-ds" = @{ hostPath = "C:\MU-Project\DataServer\LOG" }
    "mu-cs" = @{ hostPath = "C:\MU-Project\ConnectServer\LOG" }
    "mu-gs-regular" = @{ hostPath = "C:\MU-Project\GameServerRegular\Log" }
    "mu-gs-siege" = @{ hostPath = "C:\MU-Project\GameServerSiege\Log" }
    "mu-web" = @{ hostPath = "C:\MU-Project\logs-copy\mu-web" }
}

if ($service -eq "all") { $keys = $services.Keys } else { $keys = @($service) }

foreach ($key in $keys) {
    $info = $services[$key]
    $logPath = $info.hostPath
    
    Write-Host "`n=== Checking logs for: $key ===" -ForegroundColor Cyan
    Write-Host "Log path: $logPath" -ForegroundColor Gray
    
    if (Test-Path $logPath) {
        $files = Get-ChildItem -Path $logPath -File -ErrorAction SilentlyContinue
        if ($files) {
            # Sort by LastWriteTime to get the latest
            $sortedFiles = $files | Sort-Object LastWriteTime -Descending
            $latestFile = $sortedFiles[0]
            
            Write-Host "Found $($files.Count) log file(s)" -ForegroundColor Green
            Write-Host "Latest file: $($latestFile.Name) (Modified: $($latestFile.LastWriteTime))" -ForegroundColor Yellow
            
            # Show tail of the latest file
            Write-Host "`n--- Tail of $($latestFile.Name) ---" -ForegroundColor Magenta
            Get-Content $latestFile.FullName -Tail 50 -ErrorAction SilentlyContinue
            
            # Also list all log files
            Write-Host "`nAll log files (sorted by date):" -ForegroundColor Gray
            $sortedFiles | ForEach-Object { Write-Host "  $($_.Name) - $($_.LastWriteTime)" -ForegroundColor Gray }
        } else {
            Write-Host "No log files found in $logPath" -ForegroundColor Red
        }
    } else {
        Write-Host "Log path does not exist: $logPath" -ForegroundColor Red
    }
}
