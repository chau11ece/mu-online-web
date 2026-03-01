# SQL Server Troubleshooting Guide
# Run as Administrator

Write-Host "=== SQL Server Troubleshooting ===" -ForegroundColor Cyan
Write-Host ""

# Check Windows Event Logs (more accessible than SQL logs)
Write-Host "[1] Checking Windows Event Logs for SQL Server errors..." -ForegroundColor Yellow
Write-Host ""

try {
    # Check Application log for SQL Server errors
    $events = Get-EventLog -LogName Application -Source "*SQL*" -Newest 10 -ErrorAction SilentlyContinue
    if ($events) {
        Write-Host "Recent SQL Server events from Windows Event Log:" -ForegroundColor Cyan
        Write-Host ""
        foreach ($event in $events) {
            $color = if ($event.EntryType -eq 'Error') { 'Red' } elseif ($event.EntryType -eq 'Warning') { 'Yellow' } else { 'Green' }
            Write-Host "[$($event.TimeGenerated)] $($event.EntryType):" -ForegroundColor $color
            Write-Host "  $($event.Message)" -ForegroundColor White
            Write-Host ""
        }
    } else {
        Write-Host "No SQL Server events found in Application log" -ForegroundColor Yellow
    }
    
    # Also check System log
    $systemEvents = Get-EventLog -LogName System -Source "*SQL*" -Newest 5 -ErrorAction SilentlyContinue
    if ($systemEvents) {
        Write-Host "Recent SQL Server events from System log:" -ForegroundColor Cyan
        foreach ($event in $systemEvents) {
            $color = if ($event.EntryType -eq 'Error') { 'Red' } elseif ($event.EntryType -eq 'Warning') { 'Yellow' } else { 'Green' }
            Write-Host "[$($event.TimeGenerated)] $($event.EntryType): $($event.Message.Substring(0, [Math]::Min(150, $event.Message.Length)))..." -ForegroundColor $color
        }
        Write-Host ""
    }
} catch {
    Write-Host "[WARNING] Could not read event logs: $_" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[2] Checking file permissions..." -ForegroundColor Yellow

$dataPath = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA"
$masterMdf = Join-Path $dataPath "master.mdf"
$masterLdf = Join-Path $dataPath "mastlog.ldf"

if (Test-Path $masterMdf) {
    Write-Host "  master.mdf exists: [OK]" -ForegroundColor Green
    
    # Check if read-only
    $fileInfo = Get-Item $masterMdf -Force
    if ($fileInfo.IsReadOnly) {
        Write-Host "  [WARNING] master.mdf is READ-ONLY!" -ForegroundColor Red
        Write-Host "  This will prevent SQL Server from starting!" -ForegroundColor Yellow
    } else {
        Write-Host "  master.mdf is not read-only: [OK]" -ForegroundColor Green
    }
} else {
    Write-Host "  [ERROR] master.mdf NOT FOUND!" -ForegroundColor Red
}

if (Test-Path $masterLdf) {
    Write-Host "  mastlog.ldf exists: [OK]" -ForegroundColor Green
    
    $fileInfo = Get-Item $masterLdf -Force
    if ($fileInfo.IsReadOnly) {
        Write-Host "  [WARNING] mastlog.ldf is READ-ONLY!" -ForegroundColor Red
    } else {
        Write-Host "  mastlog.ldf is not read-only: [OK]" -ForegroundColor Green
    }
} else {
    Write-Host "  [ERROR] mastlog.ldf NOT FOUND!" -ForegroundColor Red
}

Write-Host ""
Write-Host "[3] Quick Fix Attempts..." -ForegroundColor Yellow

# Try to remove read-only attribute
if (Test-Path $dataPath) {
    Write-Host "  Attempting to remove read-only attributes..." -ForegroundColor Cyan
    try {
        Get-ChildItem -Path $dataPath -Recurse -File | ForEach-Object {
            if ($_.IsReadOnly) {
                $_.IsReadOnly = $false
                Write-Host "    Fixed: $($_.Name)" -ForegroundColor Green
            }
        }
        Write-Host "  [OK] Read-only attributes checked" -ForegroundColor Green
    } catch {
        Write-Host "  [WARNING] Could not fix read-only: $_" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== Recommendations ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Based on the errors above:" -ForegroundColor Yellow
Write-Host ""
Write-Host "IF files are READ-ONLY:" -ForegroundColor White
Write-Host "  1. Navigate to: C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA" -ForegroundColor Cyan
Write-Host "  2. Right-click master.mdf -> Properties -> Uncheck 'Read-only'" -ForegroundColor Cyan
Write-Host "  3. Do the same for mastlog.ldf and all other .mdf/.ldf files" -ForegroundColor Cyan
Write-Host "  4. Apply to all files in subfolders" -ForegroundColor Cyan
Write-Host ""
Write-Host "IF permission errors:" -ForegroundColor White
Write-Host "  1. Right-click DATA folder -> Properties -> Security tab" -ForegroundColor Cyan
Write-Host "  2. Add 'NT SERVICE\MSSQL`$SQLEXPRESS' with Full Control" -ForegroundColor Cyan
Write-Host "  3. Apply to all subfolders and files" -ForegroundColor Cyan
Write-Host ""
Write-Host "IF files are MISSING or CORRUPTED:" -ForegroundColor White
Write-Host "  -> Reinstall SQL Server Express 2012" -ForegroundColor Red
Write-Host ""
Write-Host "After fixing, try starting SQL Server:" -ForegroundColor Yellow
Write-Host "  Start-Service 'MSSQL`$SQLEXPRESS'" -ForegroundColor Cyan
