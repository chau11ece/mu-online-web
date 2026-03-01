# Fix-SQL2012-StartupHandle.ps1
# Fix for "Could not find the Database Engine startup handle" error

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "SQL Server 2012 Startup Handle Fix" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[ERROR] Please run this script as Administrator!" -ForegroundColor Red
    exit 1
}

Write-Host "[1/6] Checking Visual C++ Redistributables..." -ForegroundColor Yellow

# Check for Visual C++ 2010 Redistributable
$vc2010x64 = "C:\Windows\System32\vcruntime140.dll"
$vc2010x86 = "C:\Windows\SysWOW64\vcruntime140.dll"

if ((Test-Path $vc2010x64) -or (Test-Path $vc2010x86)) {
    Write-Host "  [OK] Visual C++ Redistributable found" -ForegroundColor Green
} else {
    Write-Host "  [WARN] Visual C++ Redistributable may be missing" -ForegroundColor Yellow
    Write-Host "  Download: https://www.microsoft.com/en-us/download/details.aspx?id=26999" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "[2/6] Checking .NET Framework 3.5..." -ForegroundColor Yellow

# Check .NET Framework 3.5
$net35 = Get-WindowsOptionalFeature -Online -FeatureName "NetFx3"
if ($net35.State -eq "Enabled") {
    Write-Host "  [OK] .NET Framework 3.5 is enabled" -ForegroundColor Green
} else {
    Write-Host "  [INFO] .NET Framework 3.5 may not be enabled" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "[3/6] Checking SQL Server installation directories..." -ForegroundColor Yellow

# Check SQL Server directories
$sqlPaths = @(
    "C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\Binn",
    "C:\Program Files (x86)\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\Binn"
)

foreach ($path in $sqlPaths) {
    if (Test-Path $path) {
        Write-Host "  Found: $path" -ForegroundColor Cyan
        # Check for sqlservr.exe
        if (Test-Path "$path\sqlservr.exe") {
            Write-Host "    [OK] sqlservr.exe found" -ForegroundColor Green
        } else {
            Write-Host "    [WARN] sqlservr.exe NOT found!" -ForegroundColor Red
        }
    }
}

Write-Host ""
Write-Host "[4/6] Checking Windows Event Log for SQL Server errors..." -ForegroundColor Yellow

# Check event log for SQL Server errors
try {
    $events = Get-WinEvent -FilterHashtable @{LogName="Application"; ProviderName="MSSQLSERVER"; Level=1,2,3} -MaxEvents 5 -ErrorAction SilentlyContinue
    if ($events) {
        Write-Host "  Recent SQL Server events:" -ForegroundColor Cyan
        $events | ForEach-Object {
            Write-Host "    [$($_.TimeCreated)] $($_.Message.Substring(0, [Math]::Min(100, $_.Message.Length)))..." -ForegroundColor White
        }
    } else {
        Write-Host "  No recent SQL Server events found" -ForegroundColor Cyan
    }
} catch {
    Write-Host "  Could not read event log" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[5/6] Checking user permissions..." -ForegroundColor Yellow

# Check if current user is in Administrators group
$isInAdmin = net localgroup Administrators | Select-String $env:USERNAME
if ($isInAdmin) {
    Write-Host "  [OK] User is in Administrators group" -ForegroundColor Green
} else {
    Write-Host "  [WARN] User may need Administrator privileges" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[6/6] Common fixes..." -ForegroundColor Yellow

Write-Host ""
Write-Host "FIX 1: Run SQL Server as Administrator" -ForegroundColor Cyan
Write-Host "  1. Open SQL Server Configuration Manager" -ForegroundColor White
Write-Host "  2. Right-click SQL Server (MSSQLSERVER)" -ForegroundColor White
Write-Host "  3. Click Properties" -ForegroundColor White
Write-Host "  4. Go to 'Service' tab" -ForegroundColor White
Write-Host "  5. Set 'Start Mode' to Automatic" -ForegroundColor White
Write-Host "  6. Click OK and restart service" -ForegroundColor White

Write-Host ""
Write-Host "FIX 2: Repair SQL Server Installation" -ForegroundColor Cyan
Write-Host "  1. Open Control Panel > Programs and Features" -ForegroundColor White
Write-Host "  2. Find Microsoft SQL Server 2012" -ForegroundColor White
Write-Host "  3. Click Change > Repair" -ForegroundColor White

Write-Host ""
Write-Host "FIX 3: Check Windows Temp folder permissions" -ForegroundColor Cyan
Write-Host "  icacls C:\Users\*\AppData\Local\Temp" -ForegroundColor White

Write-Host ""
Write-Host "FIX 4: Run SQL Server from command line" -ForegroundColor Cyan
Write-Host '  "C:\Program Files\Microsoft SQL Server\MSSQL11.MSSQLSERVER\MSSQL\Binn\sqlservr.exe" -c -s MSSQLSERVER' -ForegroundColor White

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "If installation failed, try:" -ForegroundColor Yellow
Write-Host "1. Install SQL Server 2012 Express with SP2" -ForegroundColor White
Write-Host "   https://www.microsoft.com/en-us/download/details.aspx?id=43351" -ForegroundColor Cyan
Write-Host "2. During install, use 'NT AUTHORITY\System' as service account" -ForegroundColor White
Write-Host "3. Disable antivirus during installation" -ForegroundColor White
Write-Host "========================================" -ForegroundColor Cyan
