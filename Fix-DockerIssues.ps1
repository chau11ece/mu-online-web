# Fix-DockerIssues.ps1
# Script to troubleshoot and fix Docker issues for MU SQL Server

param(
    [switch]$Clean,
    [switch]$Rebuild,
    [switch]$FullFix
)

$ErrorActionPreference = "Continue"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Docker Issues Troubleshooting Script" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[ERROR] Please run this script as Administrator!" -ForegroundColor Red
    exit 1
}

Write-Host "[1/6] Checking Docker service status..." -ForegroundColor Yellow
$dockerService = Get-Service -Name "docker" -ErrorAction SilentlyContinue
if ($dockerService) {
    if ($dockerService.Status -eq "Running") {
        Write-Host "  [OK] Docker service is running" -ForegroundColor Green
    } else {
        Write-Host "  [WARN] Docker service is not running. Starting..." -ForegroundColor Yellow
        Start-Service -Name "docker"
    }
} else {
    Write-Host "  [ERROR] Docker service not found. Please install Docker Desktop." -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "[2/6] Checking Windows Containers feature..." -ForegroundColor Yellow
$containersFeature = Get-WindowsOptionalFeature -Online -FeatureName "Containers" -ErrorAction SilentlyContinue
if ($containersFeature) {
    if ($containersFeature.State -eq "Enabled") {
        Write-Host "  [OK] Windows Containers feature is enabled" -ForegroundColor Green
    } else {
        Write-Host "  [ERROR] Windows Containers feature is disabled. Enable it and try again." -ForegroundColor Red
        Write-Host "  Run: Enable-WindowsOptionalFeature -Online -FeatureName Containers -All" -ForegroundColor Yellow
    }
} else {
    Write-Host "  [INFO] Could not check Containers feature (may require Server OS)" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "[3/6] Checking Docker system info..." -ForegroundColor Yellow
try {
    docker info 2>&1 | Select-Object -First 20
} catch {
    Write-Host "  [ERROR] Could not get Docker info: $_" -ForegroundColor Red
}

Write-Host ""
Write-Host "[4/6] Cleaning up Docker resources..." -ForegroundColor Yellow

# Stop all running containers
$containers = docker ps -a --format "{{.Names}}" 2>$null
if ($containers) {
    Write-Host "  Stopping containers..." -ForegroundColor Cyan
    docker stop (docker ps -a -q) 2>$null
    docker rm (docker ps -a -q) 2>$null
}

# Prune volumes
Write-Host "  Pruning volumes..." -ForegroundColor Cyan
docker volume prune -f 2>$null

# Prune networks
Write-Host "  Pruning networks..." -ForegroundColor Cyan
docker network prune -f 2>$null

# Prune builder cache
Write-Host "  Pruning builder cache..." -ForegroundColor Cyan
docker builder prune -af 2>$null

Write-Host ""
Write-Host "[5/6] Checking disk space for Docker..." -ForegroundColor Yellow
$drive = Get-PSDrive -Name C
$freeSpace = [math]::Round($drive.Free / 1GB, 2)
Write-Host "  C: drive free space: $freeSpace GB"
if ($freeSpace -lt 20) {
    Write-Host "  [WARN] Low disk space! Free up at least 20GB for Docker." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "[6/6] Setting Docker preferences for Windows containers..." -ForegroundColor Yellow

# Check if using Windows containers mode
$dockerInfo = docker info 2>&1 | Out-String
if ($dockerInfo -match "OS\/Type:\s*Windows") {
    Write-Host "  [OK] Docker is in Windows container mode" -ForegroundColor Green
} else {
    Write-Host "  [INFO] Docker may be in Linux container mode" -ForegroundColor Cyan
    Write-Host "  Note: SQL Server on .NET Framework requires Windows containers" -ForegroundColor Yellow
}

# Workaround for HCS scratch size error
Write-Host ""
Write-Host "[WORKAROUND] Applying HCS scratch size fix..." -ForegroundColor Yellow

# Check and create Docker data directory if needed
$dockerDataRoot = "C:\ProgramData\Docker"
if (Test-Path $dockerDataRoot) {
    $configFile = "$dockerDataRoot\config.json"
    if (Test-Path $configFile) {
        Write-Host "  Found config at: $configFile" -ForegroundColor Cyan
    }
}

# Set scratch size for Windows containers (registry fix)
$scratchSizePath = "HKLM:\SYSTEM\CurrentControlSet\Services\docker\Parameters"
if (-not (Test-Path $scratchSizePath)) {
    New-Item -Path $scratchSizePath -Force | Out-Null
}

# Set default scratch size to 64GB
Set-ItemProperty -Path $scratchSizePath -Name "DefaultScratchSize" -Value 67108864 -Type DWord -Force -ErrorAction SilentlyContinue
Write-Host "  Set DefaultScratchSize to 64GB in registry" -ForegroundColor Green

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Troubleshooting complete!" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

if ($Clean -or $FullFix) {
    Write-Host ""
    Write-Host "Cleaning Docker completely..." -ForegroundColor Yellow
    docker system df
    docker system prune -af --volumes
    Write-Host "  Done!" -ForegroundColor Green
}

Write-Host ""
Write-Host "Recommended next steps:" -ForegroundColor Cyan
Write-Host "1. Restart Docker service: Restart-Service docker" -ForegroundColor White
Write-Host "2. If still failing, switch to Linux containers and use SQL Server 2019 for Linux" -ForegroundColor White
Write-Host "3. Or try: docker system prune -a" -ForegroundColor White
Write-Host ""

# Attempt to rebuild if requested
if ($Rebuild -or $FullFix) {
    Write-Host "Attempting to rebuild SQL Server container..." -ForegroundColor Yellow
    Set-Location "C:\MU-Project"
    docker compose build --no-cache mu-sqlserver
    if ($LASTEXITCODE -eq 0) {
        Write-Host "Build successful! Starting container..." -ForegroundColor Green
        docker compose up -d mu-sqlserver
    } else {
        Write-Host "Build failed. Check the logs above." -ForegroundColor Red
    }
}
