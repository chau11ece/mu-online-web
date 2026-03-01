# Find all ports used by the 4 latest IGC.GameServer_R PIDs (private servers)
$allGS = Get-Process -Name 'IGC.GameServer_R' -ErrorAction SilentlyContinue | Sort-Object StartTime
$private = $allGS | Select-Object -Last 4

Write-Host "Private server PIDs:"
foreach ($p in $private) {
    Write-Host "  PID $($p.Id)  started $($p.StartTime.ToString('HH:mm:ss'))"
}

Write-Host ""
Write-Host "Netstat for those PIDs:"
$netstat = netstat -ano
foreach ($p in $private) {
    $lines = $netstat | Select-String "\s+$($p.Id)$"
    Write-Host "  PID $($p.Id):"
    if ($lines) {
        $lines | ForEach-Object { Write-Host "    $_" }
    } else {
        Write-Host "    (no netstat entries)"
    }
}
