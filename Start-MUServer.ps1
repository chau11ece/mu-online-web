# MU Online Server Startup Script
# Run PowerShell as Administrator for SQL Server startup

param(
    [switch]$Stop,
    [ValidateSet("all","pub","prv1","prv2")]
    [string]$Group = "all"
)

# Load .env file for IP configuration
function Load-EnvFile {
    $envFile = Join-Path $PSScriptRoot ".env"
    if (Test-Path $envFile) {
        Get-Content $envFile | ForEach-Object {
            if ($_ -match '^([^=]+)=(.*)$') {
                $name = $_.Split('=')[0].Trim()
                $value = $_.Split('=', 2)[1].Trim()
                [Environment]::SetEnvironmentVariable($name, $value, 'Process')
            }
        }
    }
}

# Function to update IP addresses in config files
function Update-ConfigIPs {
    param(
        [string]$OldIP,
        [string]$NewIP
    )
    
    if ($OldIP -eq $NewIP) {
        return
    }
    
    Write-Host "  Updating config files with IP: $NewIP" -ForegroundColor Cyan
    
    # Config files to update
    $configFiles = @(
        "DataServer\IGCDS.ini",
        "DataServer\IGC_AllowedIPList.xml",
        "ConnectServer\IGCCS.ini",
        "ConnectServer\IGC_ServerList.xml",
        "IGCData\IGC_MapServerInfo.xml",
        "GameServerRegular\GameServer.ini",
        "GameServerSiege\GameServer.ini",
        "GameServer_Code1\GameServer.ini",
        "GameServer_Code2\GameServer.ini",
        "GameServer_Code3\GameServer.ini",
        "GameServer_Code20\GameServer.ini",
        "GameServer_Code21\GameServer.ini",
        "GameServer_Code40\GameServer.ini",
        "GameServer_Code41\GameServer.ini"
    )
    
    $updatedCount = 0
    foreach ($file in $configFiles) {
        $filePath = Join-Path $PSScriptRoot $file
        if (Test-Path $filePath) {
            $content = Get-Content $filePath -Raw
            if ($content -match [regex]::Escape($OldIP)) {
                $content = $content -replace [regex]::Escape($OldIP), $NewIP
                Set-Content -Path $filePath -Value $content -NoNewline
                Write-Host "    Updated: $file" -ForegroundColor Green
                $updatedCount++
            }
        }
    }
    
    if ($updatedCount -gt 0) {
        Write-Host "  Updated $updatedCount config file(s)" -ForegroundColor Green
    } else {
        Write-Host "  No config files needed updating" -ForegroundColor Gray
    }
}

# Check for administrator privileges
function Test-IsAdmin {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

$isAdmin = Test-IsAdmin

if (-not $isAdmin) {
    Write-Host "[WARNING] Not running as Administrator. Process termination may fail." -ForegroundColor Yellow
    Write-Host "         Please restart PowerShell as Administrator for full functionality." -ForegroundColor Yellow
    Write-Host ""
}

Write-Host "=== MU Online Server Startup Script ===" -ForegroundColor Cyan
Write-Host ""

# - Server definitions -
# Group: "core" = always started | "pub" = DaNang Public | "prv1"/"prv2" = Private
$applications = @(
    @{ Group="core"; Name="DataServer";         Exe="IGC.DataServer.exe";   Dir="DataServer";         Port=56960; Delay=5 },
    @{ Group="core"; Name="ConnectServer";      Exe="IGC.ConnectServer.exe";Dir="ConnectServer";       Port=44405; Delay=3 },

    # DaNang (Public) - Codes 0,1,2,3,14
    @{ Group="pub";  Name="DaNang-Pub0 Normal"; Exe="IGC.GameServer_R.exe"; Dir="GameServerRegular";   Port=55901; Delay=2 },
    @{ Group="pub";  Name="DaNang-Pub1 Normal"; Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code1";    Port=55902; Delay=2 },
    @{ Group="pub";  Name="DaNang-Pub2 VIP";    Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code2";    Port=55903; Delay=2 },
    @{ Group="pub";  Name="DaNang-Pub3 NonPvP"; Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code3";    Port=55904; Delay=2 },
    @{ Group="pub";  Name="DaNang-Siege";       Exe="IGC.GameServer_C.exe"; Dir="GameServerSiege";     Port=55919; Delay=2 },

    # DaNang (Private) Group 1 - Codes 20,21
    @{ Group="prv1"; Name="DaNang-Prv1A";       Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code20";   Port=55920; Delay=2 },
    @{ Group="prv1"; Name="DaNang-Prv1B";       Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code21";   Port=55921; Delay=2 },

    # DaNang (Private) Group 2 - Codes 40,41
    @{ Group="prv2"; Name="DaNang-Prv2A";       Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code40";   Port=55940; Delay=2 },
    @{ Group="prv2"; Name="DaNang-Prv2B";       Exe="IGC.GameServer_R.exe"; Dir="GameServer_Code41";   Port=55941; Delay=2 }
)

$BASE = "C:\MU-Project"

# Filter by -Group parameter
$activeApps = $applications | Where-Object { $_.Group -eq "core" -or $_.Group -eq $Group -or $Group -eq "all" }

# Function to stop all servers - kills by EXE name to catch all instances including zombies
function Stop-AllServers {
    Write-Host "=== Stopping All MU Servers ===" -ForegroundColor Yellow
    Write-Host ""

    $exeNames = @('IGC.DataServer', 'IGC.ConnectServer', 'IGC.GameServer_R', 'IGC.GameServer_C')
    $stoppedCount = 0

    foreach ($name in $exeNames) {
        $procs = Get-Process -Name $name -ErrorAction SilentlyContinue
        foreach ($proc in $procs) {
            try {
                Write-Host "  Stopping $($proc.Name)  PID $($proc.Id)" -ForegroundColor Cyan
                Stop-Process -Id $proc.Id -Force -ErrorAction Stop
                Write-Host "    [OK] Stopped" -ForegroundColor Green
                $stoppedCount++
            } catch {
                Write-Host "    [WARN] Could not stop PID $($proc.Id): $_" -ForegroundColor Yellow
            }
        }
    }

    if ($stoppedCount -eq 0) {
        Write-Host "  No MU server processes found running." -ForegroundColor Gray
    }

    Write-Host ""
    Write-Host "=== Stopped $stoppedCount process(es) ===" -ForegroundColor Cyan
}

# Function to start SQL Server
function Start-SQLServer {
    # Auto-detect running SQL Server instance
    $sqlServiceName = $null
    $sqlInstance = $null

    $runningSqlService = Get-Service | Where-Object { $_.Name -like 'MSSQL*' -and $_.Name -notlike '*FDLauncher*' -and $_.Status -eq 'Running' } | Select-Object -First 1
    if ($runningSqlService) {
        $sqlServiceName = $runningSqlService.Name
        if ($runningSqlService.Name -eq 'MSSQLSERVER') {
            $sqlInstance = "localhost"
        } elseif ($runningSqlService.Name -match 'MSSQL\$(.+)') {
            $sqlInstance = "localhost\$($Matches[1])"
        }
        Write-Host "[DETECTED] Running SQL Server: $sqlInstance (Service: $sqlServiceName)" -ForegroundColor Green
    }

    # Step 1: Start SQL Server Service
    Write-Host ""
    Write-Host "[1/2] Starting SQL Server..." -ForegroundColor Yellow
    $sqlStarted = $false

    # Try the detected service first, then fallbacks
    $servicesToTry = @()
    if ($sqlServiceName) {
        $servicesToTry += $sqlServiceName
    }
    $servicesToTry += @('MSSQL$MSSQLSERVER01', 'MSSQL$SQLEXPRESS', 'MSSQLSERVER')

    foreach ($svcName in $servicesToTry) {
        try {
            $sqlService = Get-Service -Name $svcName -ErrorAction SilentlyContinue
            if ($sqlService) {
                if ($sqlService.Status -eq 'Running') {
                    Write-Host "  SQL Server ($svcName) is already running." -ForegroundColor Green
                    $sqlStarted = $true
                } else {
                    Write-Host "  Attempting to start SQL Server ($svcName)..." -ForegroundColor Cyan
                    Start-Service -Name $svcName -ErrorAction Stop
                    Start-Sleep -Seconds 3
                    $sqlService.Refresh()
                    if ($sqlService.Status -eq 'Running') {
                        Write-Host "  SQL Server ($svcName) started successfully!" -ForegroundColor Green
                        $sqlStarted = $true
                        Start-Sleep -Seconds 2
                    } else {
                        Write-Host "  WARNING: SQL Server status is: $($sqlService.Status)" -ForegroundColor Yellow
                    }
                }
                break
            }
        } catch {
            # Service doesn't exist, try next
        }
    }

    if (-not $sqlStarted) {
        Write-Host "  WARNING: Could not start any SQL Server service." -ForegroundColor Yellow
        Write-Host ""
        Write-Host "  Troubleshooting steps:" -ForegroundColor Yellow
        Write-Host "  1. Make sure you run PowerShell as Administrator" -ForegroundColor White
        Write-Host "  2. Run: .\Test-SQLServer.ps1 for detailed diagnostics" -ForegroundColor White
        Write-Host "  3. Check SQL Server error logs manually" -ForegroundColor White
        Write-Host "  4. Try starting SQL Server from Services.msc" -ForegroundColor White
        Write-Host ""
        $response = Read-Host "  Continue anyway? (Y/N)"
        if ($response -ne 'Y' -and $response -ne 'y') {
            Write-Host "  Aborted." -ForegroundColor Yellow
            return
        }
        Write-Host "  Continuing without SQL Server verification..." -ForegroundColor Yellow
    }

    return $sqlStarted
}

# Helper: check if a TCP port is already bound
function Test-PortBound($port) {
    $listeners = netstat -ano | Select-String "TCP\s+\S+:$port\s+\S+\s+LISTENING"
    return ($null -ne $listeners)
}

# Function to start all servers in order
function Start-AllServers {
    Write-Host ""
    Write-Host "[2/2] Starting MU Server Applications..." -ForegroundColor Yellow
    Write-Host "  Servers will run in WINDOWED mode for debugging" -ForegroundColor Cyan
    Write-Host ""

    $startedCount = 0
    $skippedCount = 0
    $failedCount = 0

    # Start in order: DataServer -> ConnectServer -> GameServers
    foreach ($app in $activeApps) {
        $exePath    = Join-Path $BASE "$($app.Dir)\$($app.Exe)"
        $workingDir = Join-Path $BASE $app.Dir
        $label      = "$($app.Name)  [port $($app.Port)]"

        # Skip if port already bound - server is already running
        if (Test-PortBound $app.Port) {
            Write-Host "  [SKIP] $label - port already in use (already running)" -ForegroundColor Yellow
            $skippedCount++
            continue
        }

        Write-Host "  Starting $label..." -ForegroundColor Cyan

        if (-not (Test-Path $exePath)) {
            Write-Host "    [FAIL] File not found: $exePath" -ForegroundColor Red
            $failedCount++
            continue
        }

        try {
            $process = Start-Process -FilePath $exePath `
                -WorkingDirectory $workingDir `
                -PassThru -WindowStyle Normal -ErrorAction Stop

            Start-Sleep -Seconds $app.Delay

            if (-not $process.HasExited) {
                Write-Host "    [OK] PID $($process.Id)" -ForegroundColor Green
                $startedCount++
            } else {
                Write-Host "    [FAIL] Process exited immediately - check console window" -ForegroundColor Red
                $failedCount++
            }
        } catch {
            Write-Host "    [FAIL] $_" -ForegroundColor Red
            $failedCount++
        }
    }

    # Final verification - check by port
    Write-Host ""
    Write-Host "=== Verifying Running Servers ===" -ForegroundColor Cyan
    $runningServers = 0
    foreach ($app in $activeApps) {
        if (Test-PortBound $app.Port) {
            Write-Host "  [UP]   $($app.Name)  port $($app.Port)" -ForegroundColor Green
            $runningServers++
        } else {
            Write-Host "  [DOWN] $($app.Name)  port $($app.Port)" -ForegroundColor Red
        }
    }

    Write-Host ""
    Write-Host "=== Startup Complete ===" -ForegroundColor Cyan
    Write-Host "  Started: $startedCount | Skipped: $skippedCount | Running: $runningServers" -ForegroundColor Green
    if ($failedCount -gt 0) { Write-Host "  Failed: $failedCount" -ForegroundColor Red }
    Write-Host ""
    Write-Host "Private server whitelists:" -ForegroundColor Cyan
    Write-Host "  Group 1: C:\MU-Project\GameServer_Code20\ConnectMember.txt" -ForegroundColor White
    Write-Host "  Group 2: C:\MU-Project\GameServer_Code40\ConnectMember.txt" -ForegroundColor White
}

# Main execution - Two modes: Start or Stop
if ($Stop) {
    if (-not $isAdmin) {
        Write-Host "[WARN] Not running as Administrator - some processes may not stop." -ForegroundColor Yellow
        Write-Host ""
    }
    Stop-AllServers
} else {
    # Default: Start servers only (without stopping first)
    
    # Load .env file and update config IPs
    Load-EnvFile
    $currentIP = $env:PUBLIC_IP
    if ($currentIP) {
        Write-Host "[CONFIG] Using IP from .env: $currentIP" -ForegroundColor Cyan
        # Get the old IP from first config file as reference
        $sampleConfig = Join-Path $PSScriptRoot "DataServer\IGCDS.ini"
        $oldIP = "192.168.1.252"  # Default old IP
        if (Test-Path $sampleConfig) {
            $content = Get-Content $sampleConfig -Raw
            if ($content -match '(\d+\.\d+\.\d+\.\d+)') {
                $foundIP = $matches[1]
                if ($foundIP -ne $currentIP) {
                    $oldIP = $foundIP
                }
            }
        }
        Update-ConfigIPs -OldIP $oldIP -NewIP $currentIP
    }
    
    $null = Start-SQLServer
    Start-AllServers
}
