Set-Location "C:\MU-Project"

function Start-MUApp($name, $dir, $exe, $delaySec) {
    $full = "C:\MU-Project\$dir"
    Write-Host "Starting $name ..." -NoNewline
    Start-Process -FilePath "$full\$exe" -WorkingDirectory $full -WindowStyle Minimized
    Start-Sleep -Seconds $delaySec
    Write-Host " done"
}

# Core
Start-MUApp "DataServer"       "DataServer"       "IGC.DataServer.exe"   5
Start-MUApp "ConnectServer"    "ConnectServer"    "IGC.ConnectServer.exe" 3

# Public servers
Start-MUApp "Pub0 Normal"      "GameServerRegular"  "IGC.GameServer_R.exe" 2
Start-MUApp "Pub1 Normal"      "GameServer_Code1"   "IGC.GameServer_R.exe" 2
Start-MUApp "Pub2 VIP"         "GameServer_Code2"   "IGC.GameServer_R.exe" 2
Start-MUApp "Pub3 NonPvP"      "GameServer_Code3"   "IGC.GameServer_R.exe" 2
Start-MUApp "Pub Siege"        "GameServerSiege"    "IGC.GameServer_C.exe" 2

# Private servers
Start-MUApp "Prv1A (Code20)"   "GameServer_Code20"  "IGC.GameServer_R.exe" 2
Start-MUApp "Prv1B (Code21)"   "GameServer_Code21"  "IGC.GameServer_R.exe" 2
Start-MUApp "Prv2A (Code40)"   "GameServer_Code40"  "IGC.GameServer_R.exe" 2
Start-MUApp "Prv2B (Code41)"   "GameServer_Code41"  "IGC.GameServer_R.exe" 2

Write-Host ""
Write-Host "All servers launched. Waiting 30s before status check..."
Start-Sleep -Seconds 30

# Quick port check
$muPorts = @(56960,44405,55901,55902,55903,55904,55919,55920,55921,55940,55941)
$listening = netstat -ano | Select-String "LISTENING"
Write-Host ""
Write-Host "=== Port Status (30s after start) ==="
foreach ($port in $muPorts) {
    $found = $listening | Where-Object { $_ -match ":$port\s" }
    $status = if ($found) { "UP" } else { "DOWN" }
    Write-Host "  Port $port : $status"
}
