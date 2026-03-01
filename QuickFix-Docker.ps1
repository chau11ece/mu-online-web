# QuickFix-Docker.ps1 - Run these commands in PowerShell as Administrator

# Fix 1: Set scratch size in registry (may require admin)
$scratchSizePath = "HKLM:\SYSTEM\CurrentControlSet\Services\docker\Parameters"
if (-not (Test-Path $scratchSizePath)) {
    New-Item -Path $scratchSizePath -Force | Out-Null
}
Set-ItemProperty -Path $scratchSizePath -Name "DefaultScratchSize" -Value 67108864 -Type DWord -Force
Write-Host "Registry fix applied"

# Fix 2: Clean Docker
Write-Host "Cleaning Docker..."
docker system prune -af --volumes

# Fix 3: Restart Docker
Write-Host "Restarting Docker service..."
Restart-Service docker -Force

Write-Host "Done! Try rebuilding: docker compose build --no-cache mu-sqlserver"
