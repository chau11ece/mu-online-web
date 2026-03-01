# Fix SQL Server Error 3417 - Master Database Access Issue
# Run as Administrator

Write-Host "=== SQL Server Error 3417 Fix ===" -ForegroundColor Cyan
Write-Host "Error 3417: Cannot access master database files" -ForegroundColor Yellow
Write-Host ""

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[ERROR] This script MUST be run as Administrator!" -ForegroundColor Red
    Write-Host "Right-click PowerShell -> Run as Administrator" -ForegroundColor Yellow
    exit 1
}

$dataPath = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA"
$masterMdf = Join-Path $dataPath "master.mdf"
$masterLdf = Join-Path $dataPath "mastlog.ldf"

Write-Host "[Step 1] Checking master database files..." -ForegroundColor Yellow

# Check if files exist
if (-not (Test-Path $masterMdf)) {
    Write-Host "  [ERROR] master.mdf NOT FOUND at: $masterMdf" -ForegroundColor Red
    Write-Host "  SQL Server installation appears corrupted!" -ForegroundColor Red
    Write-Host "  -> RECOMMENDATION: Reinstall SQL Server Express 2012" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $masterLdf)) {
    Write-Host "  [ERROR] mastlog.ldf NOT FOUND at: $masterLdf" -ForegroundColor Red
    Write-Host "  SQL Server installation appears corrupted!" -ForegroundColor Red
    Write-Host "  -> RECOMMENDATION: Reinstall SQL Server Express 2012" -ForegroundColor Yellow
    exit 1
}

Write-Host "  [OK] Both master database files exist" -ForegroundColor Green

# Check file attributes
Write-Host ""
Write-Host "[Step 2] Checking file attributes..." -ForegroundColor Yellow

$mdfFile = Get-Item $masterMdf -Force
$ldfFile = Get-Item $masterLdf -Force

$issues = @()

if ($mdfFile.IsReadOnly) {
    Write-Host "  [ISSUE] master.mdf is READ-ONLY" -ForegroundColor Red
    $issues += "readonly"
}

if ($ldfFile.IsReadOnly) {
    Write-Host "  [ISSUE] mastlog.ldf is READ-ONLY" -ForegroundColor Red
    $issues += "readonly"
}

# Check file sizes (corrupted files might be 0 bytes)
if ($mdfFile.Length -eq 0) {
    Write-Host "  [ISSUE] master.mdf is 0 bytes (corrupted?)" -ForegroundColor Red
    $issues += "corrupted"
}

if ($ldfFile.Length -eq 0) {
    Write-Host "  [ISSUE] mastlog.ldf is 0 bytes (corrupted?)" -ForegroundColor Red
    $issues += "corrupted"
}

if ($issues.Count -eq 0) {
    Write-Host "  [OK] File attributes look normal" -ForegroundColor Green
}

# Fix read-only attributes
Write-Host ""
Write-Host "[Step 3] Fixing file attributes..." -ForegroundColor Yellow

try {
    # Remove read-only from all database files
    Get-ChildItem -Path $dataPath -Recurse -File | ForEach-Object {
        if ($_.IsReadOnly) {
            $_.IsReadOnly = $false
            Write-Host "  Fixed read-only: $($_.Name)" -ForegroundColor Green
        }
    }
    Write-Host "  [OK] Read-only attributes fixed" -ForegroundColor Green
} catch {
    Write-Host "  [WARNING] Could not fix read-only: $_" -ForegroundColor Yellow
}

# Fix permissions
Write-Host ""
Write-Host "[Step 4] Fixing file permissions..." -ForegroundColor Yellow

try {
    $serviceAccount = "NT SERVICE\MSSQL`$SQLEXPRESS"
    
    # Fix DATA directory permissions
    $acl = Get-Acl $dataPath
    $permission = $serviceAccount, "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
    $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule $permission
    $acl.SetAccessRule($accessRule)
    Set-Acl $dataPath $acl
    
    # Also grant to Administrators
    $adminPerm = "BUILTIN\Administrators", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
    $adminRule = New-Object System.Security.AccessControl.FileSystemAccessRule $adminPerm
    $acl.SetAccessRule($adminRule)
    Set-Acl $dataPath $acl
    
    Write-Host "  [OK] Permissions fixed on DATA directory" -ForegroundColor Green
    
    # Fix LOG directory
    $logPath = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log"
    if (Test-Path $logPath) {
        $aclLog = Get-Acl $logPath
        $aclLog.SetAccessRule($accessRule)
        $aclLog.SetAccessRule($adminRule)
        Set-Acl $logPath $aclLog
        Write-Host "  [OK] Permissions fixed on LOG directory" -ForegroundColor Green
    }
    
} catch {
    Write-Host "  [WARNING] Could not fix permissions: $_" -ForegroundColor Yellow
    Write-Host "  You may need to manually set permissions:" -ForegroundColor Yellow
    Write-Host "    1. Right-click DATA folder -> Properties -> Security" -ForegroundColor Cyan
    Write-Host "    2. Add: NT SERVICE\MSSQL`$SQLEXPRESS with Full Control" -ForegroundColor Cyan
}

# Check if files are locked
Write-Host ""
Write-Host "[Step 5] Checking if files are locked..." -ForegroundColor Yellow

try {
    $file = [System.IO.File]::Open($masterMdf, 'Open', 'ReadWrite', 'None')
    $file.Close()
    Write-Host "  [OK] Files are not locked by another process" -ForegroundColor Green
} catch {
    Write-Host "  [WARNING] Files may be locked: $_" -ForegroundColor Yellow
    Write-Host "  Make sure no other SQL Server instances are running" -ForegroundColor Yellow
}

# Check disk space
Write-Host ""
Write-Host "[Step 6] Checking disk space..." -ForegroundColor Yellow

$drive = (Get-Item $dataPath).PSDrive
$freeSpace = [math]::Round($drive.Free / 1GB, 2)
if ($freeSpace -lt 1) {
    Write-Host "  [WARNING] Low disk space: $freeSpace GB free" -ForegroundColor Yellow
} else {
    Write-Host "  [OK] Disk space available: $freeSpace GB" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== Fix Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Attempting to start SQL Server..." -ForegroundColor Yellow

try {
    Start-Service -Name 'MSSQL$SQLEXPRESS' -ErrorAction Stop
    Start-Sleep -Seconds 3
    
    $svc = Get-Service -Name 'MSSQL$SQLEXPRESS'
    $svc.Refresh()
    
    if ($svc.Status -eq 'Running') {
        Write-Host "[SUCCESS] SQL Server started successfully!" -ForegroundColor Green
    } else {
        Write-Host "[FAILED] SQL Server status: $($svc.Status)" -ForegroundColor Red
        Write-Host ""
        Write-Host "If still failing, try these options:" -ForegroundColor Yellow
        Write-Host "1. Check Windows Event Viewer for detailed error" -ForegroundColor White
        Write-Host "2. Master database may be corrupted - rebuild required" -ForegroundColor White
        Write-Host "3. Reinstall SQL Server Express 2012" -ForegroundColor White
    }
} catch {
    Write-Host "[FAILED] Could not start SQL Server: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Error 3417 persists. Next steps:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "OPTION 1: Rebuild Master Database (Advanced)" -ForegroundColor Cyan
    Write-Host "  Run SQL Server setup in rebuild mode" -ForegroundColor White
    Write-Host ""
    Write-Host "OPTION 2: Reinstall SQL Server (Recommended)" -ForegroundColor Cyan
    Write-Host "  1. Backup any existing databases first" -ForegroundColor White
    Write-Host "  2. Uninstall SQL Server Express 2012" -ForegroundColor White
    Write-Host "  3. Download and reinstall from Microsoft" -ForegroundColor White
    Write-Host "  4. Use same instance name: SQLEXPRESS" -ForegroundColor White
}

Write-Host ""
