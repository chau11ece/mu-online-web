param (
    [int]$daysToKeep = 30,  # Keep logs from the last X days (based on filename date)
    [int]$filesToKeep = 3   # Always keep at least the last N files regardless of age
)

# Log directories to clean
$logDirs = @(
    "C:\MU-Project\DataServer\LOG",
    "C:\MU-Project\ConnectServer\LOG",
    "C:\MU-Project\GameServerRegular\Log",
    "C:\MU-Project\GameServerSiege\Log",
    "C:\MU-Project\GENS_RANKING_LOG",
    "C:\MU-Project\SQL_LOG",
    "C:\MU-Project\LOG",  # General LOG
    "C:\MU-Project\logs-copy"  # Clean copied logs (recursive)
)

# Function to parse date from filename (format: DD.MM.YYYY_Something.log)
function Parse-LogDate {
    param([string]$filename)
    
    # Extract date from filename (format: DD.MM.YYYY)
    if ($filename -match '(\d{2})\.(\d{2})\.(\d{4})') {
        $day = $matches[1]
        $month = $matches[2]
        $year = $matches[3]
        
        try {
            return Get-Date -Year $year -Month $month -Day $day -Hour 23 -Minute 59 -Second 59
        } catch {
            return $null
        }
    }
    return $null
}

foreach ($dir in $logDirs) {
    if (Test-Path $dir) {
        Write-Host "`nProcessing: $dir" -ForegroundColor Cyan
        
        $files = Get-ChildItem -Path $dir -File | Sort-Object LastWriteTime -Descending
        
        if ($files.Count -eq 0) {
            Write-Host "  No files found" -ForegroundColor Gray
            continue
        }
        
        $filesToDelete = @()
        $filesKeptByDate = @()
        
        foreach ($file in $files) {
            $logDate = Parse-LogDate $file.Name
            
            if ($logDate) {
                $daysOld = ((Get-Date) - $logDate).Days
                
                if ($daysOld -gt $daysToKeep) {
                    $filesToDelete += $file
                } else {
                    $filesKeptByDate += $file
                }
            } else {
                # If we can't parse the date, keep it (safeguard)
                $filesKeptByDate += $file
            }
        }
        
        # Also ensure we keep at least $filesToKeep files
        $totalToKeep = $filesKeptByDate + $files | Select-Object -First $filesToKeep | Select-Object -Unique
        
        # Determine final list of files to delete
        $finalFilesToDelete = $filesToDelete | Where-Object { $totalToKeep -notcontains $_ }
        
        if ($finalFilesToDelete.Count -gt 0) {
            Write-Host "  Deleting $($finalFilesToDelete.Count) old file(s)..." -ForegroundColor Yellow
            foreach ($file in $finalFilesToDelete) {
                Remove-Item $file.FullName -Force -ErrorAction SilentlyContinue
                Write-Host "    Deleted: $($file.Name)" -ForegroundColor Gray
            }
        } else {
            Write-Host "  No files to delete (all within $daysToKeep days or protected)" -ForegroundColor Green
        }
        
        Write-Host "  Files kept: $($totalToKeep.Count)" -ForegroundColor Green
    } else {
        Write-Host "Dir not found: $dir" -ForegroundColor Yellow
    }
}

Write-Host "`n=== Cleanup Complete ===" -ForegroundColor Cyan
