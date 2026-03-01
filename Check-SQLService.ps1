# Simple SQL Server Service Checker
# Run this in PowerShell as Administrator

Write-Host "=== SQL Server Service Check ===" -ForegroundColor Cyan
Write-Host ""

# Check for SQL services
Write-Host "Finding SQL Server services..." -ForegroundColor Yellow
$services = Get-Service | Where-Object { $_.Name -like '*MSSQL*' }
if ($services) {
    $services | Format-Table Name, Status, StartType, DisplayName -AutoSize
} else {
    Write-Host "No SQL Server services found!" -ForegroundColor Red
    exit
}

Write-Host ""

# Try to get SQLEXPRESS service
Write-Host "Checking MSSQL`$SQLEXPRESS..." -ForegroundColor Yellow
try {
    $svc = Get-Service -Name 'MSSQL$SQLEXPRESS' -ErrorAction Stop
    Write-Host "Service Name: $($svc.Name)" -ForegroundColor Green
    Write-Host "Status: $($svc.Status)" -ForegroundColor $(if ($svc.Status -eq 'Running') { 'Green' } else { 'Yellow' })
    Write-Host "Start Type: $($svc.StartType)" -ForegroundColor Cyan
    
    if ($svc.Status -eq 'Stopped') {
        Write-Host ""
        Write-Host "Service is stopped. Attempting to start..." -ForegroundColor Yellow
        
        # Check what's preventing startup
        Write-Host "Checking service dependencies..." -ForegroundColor Cyan
        $required = $svc.ServicesDependedOn
        if ($required) {
            Write-Host "Required services:" -ForegroundColor Cyan
            foreach ($req in $required) {
                $reqSvc = Get-Service -Name $req.Name -ErrorAction SilentlyContinue
                if ($reqSvc) {
                    Write-Host "  - $($req.Name): $($reqSvc.Status)" -ForegroundColor $(if ($reqSvc.Status -eq 'Running') { 'Green' } else { 'Red' })
                }
            }
        }
        
        Write-Host ""
        Write-Host "Starting service..." -ForegroundColor Yellow
        try {
            Start-Service -Name 'MSSQL$SQLEXPRESS' -ErrorAction Stop
            Start-Sleep -Seconds 3
            $svc.Refresh()
            if ($svc.Status -eq 'Running') {
                Write-Host "[SUCCESS] SQL Server started!" -ForegroundColor Green
            } else {
                Write-Host "[WARNING] Service status: $($svc.Status)" -ForegroundColor Yellow
            }
        } catch {
            Write-Host "[ERROR] Failed to start: $($_.Exception.Message)" -ForegroundColor Red
            Write-Host ""
            Write-Host "Common causes:" -ForegroundColor Yellow
            Write-Host "1. SQL Server files are corrupted or missing" -ForegroundColor White
            Write-Host "2. Port 1433 is already in use" -ForegroundColor White
            Write-Host "3. SQL Server data files are locked or missing" -ForegroundColor White
            Write-Host "4. Insufficient permissions (even as admin)" -ForegroundColor White
            Write-Host ""
            Write-Host "Try checking SQL Server error logs at:" -ForegroundColor Cyan
            Write-Host "C:\Program Files\Microsoft SQL Server\MSSQL*.SQLEXPRESS\MSSQL\Log\ERRORLOG" -ForegroundColor White
        }
    } else {
        Write-Host "[OK] SQL Server is already running!" -ForegroundColor Green
    }
} catch {
    Write-Host "[ERROR] $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
