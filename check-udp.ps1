Write-Host "=== ConnectServer Process ==="
$cs = Get-Process -Name 'IGC.ConnectServer' -ErrorAction SilentlyContinue
if ($cs) { Write-Host "  PID: $($cs.Id)  StartTime: $($cs.StartTime.ToString('HH:mm:ss'))" }
else { Write-Host "  NOT running" }

Write-Host ""
Write-Host "=== UDP sockets around port 55667 ==="
netstat -ano | Select-String "UDP.*556[5-9][0-9]|UDP.*557[0-9][0-9]"

Write-Host ""
Write-Host "=== All UDP sockets for ConnectServer PID ==="
if ($cs) {
    netstat -ano | Select-String "UDP.*\s+$($cs.Id)$"
}

Write-Host ""
Write-Host "=== UDP sockets for private server PIDs ==="
$allGS = Get-Process -Name 'IGC.GameServer_R' -ErrorAction SilentlyContinue | Sort-Object StartTime
$private = $allGS | Select-Object -Last 4
foreach ($p in $private) {
    $udp = netstat -ano | Select-String "UDP.*\s+$($p.Id)$"
    Write-Host "  PID $($p.Id) UDP: $udp"
}
