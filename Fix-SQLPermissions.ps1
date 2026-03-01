# Fix SQL Server File Permissions
# Run as Administrator

Write-Host "=== SQL Server Permission Fix ===" -ForegroundColor Cyan
Write-Host ""

# Check if running as admin
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[ERROR] This script must be run as Administrator!" -ForegroundColor Red
    exit 1
}

$dataPath = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\DATA"
$logPath = "C:\Program Files\Microsoft SQL Server\MSSQL11.SQLEXPRESS\MSSQL\Log"

Write-Host "Checking SQL Server data files..." -ForegroundColor Yellow

# Check if files exist
$masterMdf = Join-Path $dataPath "master.mdf"
$masterLdf = Join-Path $dataPath "mastlog.ldf"

if (-not (Test-Path $masterMdf)) {
    Write-Host "[ERROR] master.mdf not found at: $masterMdf" -ForegroundColor Red
    Write-Host "SQL Server installation may be corrupted. Reinstall may be needed." -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path $masterLdf)) {
    Write-Host "[ERROR] mastlog.ldf not found at: $masterLdf" -ForegroundColor Red
    Write-Host "SQL Server installation may be corrupted. Reinstall may be needed." -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] Database files exist" -ForegroundColor Green

# Check current permissions
Write-Host ""
Write-Host "Checking file permissions..." -ForegroundColor Yellow
try {
    $acl = Get-Acl $masterMdf
    Write-Host "Current permissions on master.mdf:" -ForegroundColor Cyan
    $acl.Access | ForEach-Object {
        Write-Host "  $($_.IdentityReference): $($_.FileSystemRights)" -ForegroundColor White
    }
} catch {
    Write-Host "[WARNING] Could not read permissions: $_" -ForegroundColor Yellow
}

# Check if files are locked
Write-Host ""
Write-Host "Checking if files are locked by another process..." -ForegroundColor Yellow
$locked = $false
try {
    $file = [System.IO.File]::Open($masterMdf, 'Open', 'ReadWrite', 'None')
    $file.Close()
    Write-Host "[OK] Files are not locked" -ForegroundColor Green
} catch {
    Write-Host "[WARNING] Files may be locked: $_" -ForegroundColor Yellow
    $locked = $true
}

# Fix permissions
Write-Host ""
Write-Host "Attempting to fix permissions..." -ForegroundColor Yellow

$serviceAccount = "NT SERVICE\MSSQL`$SQLEXPRESS"
$serviceAccountAlt = "NT AUTHORITY\NETWORK SERVICE"

try {
    # Grant full control to SQL Server service account
    Write-Host "Granting permissions to SQL Server service account..." -ForegroundColor Cyan
    
    $acl = Get-Acl $dataPath
    $permission = $serviceAccount, "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
    $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule $permission
    $acl.SetAccessRule($accessRule)
    Set-Acl $dataPath $acl
    
    # Also grant to NETWORK SERVICE (sometimes needed)
    $permission2 = $serviceAccountAlt, "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
    $accessRule2 = New-Object System.Security.AccessControl.FileSystemAccessRule $permission2
    $acl.SetAccessRule($accessRule2)
    Set-Acl $dataPath $acl
    
    Write-Host "[OK] Permissions updated on data directory" -ForegroundColor Green
    
    # Fix log directory too
    $aclLog = Get-Acl $logPath
    $aclLog.SetAccessRule($accessRule)
    $aclLog.SetAccessRule($accessRule2)
    Set-Acl $logPath $aclLog
    Write-Host "[OK] Permissions updated on log directory" -ForegroundColor Green
    
} catch {
    Write-Host "[ERROR] Could not fix permissions: $_" -ForegroundColor Red
    Write-Host "You may need to manually set permissions in Windows Explorer" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Fix Complete ===" -ForegroundColor Cyan
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Try starting SQL Server service again" -ForegroundColor White
Write-Host "2. Run: Start-Service 'MSSQL`$SQLEXPRESS'" -ForegroundColor Cyan
Write-Host "3. If still failing, check Windows Event Viewer for detailed errors" -ForegroundColor White
Write-Host "4. If files are corrupted, reinstall may be necessary" -ForegroundColor White
