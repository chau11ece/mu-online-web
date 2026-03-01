$ports = @(56960,44405,55901,55902,55903,55904,55919,55920,55921,55940,55941)
$netstat = netstat -ano
Write-Host "=== MU Server Port Status ==="
foreach ($p in $ports) {
    # Match port regardless of bound IP (handles both 0.0.0.0 and specific IP)
    $match = $netstat | Select-String "\S+:${p}\s+\S+\s+LISTENING"
    if ($match) {
        $line = ($match | Select-Object -First 1).ToString().Trim()
        $pid2 = ($line -split '\s+')[-1]
        Write-Host "  :$p  UP  (PID $pid2)" -ForegroundColor Green
    } else {
        Write-Host "  :$p  DOWN" -ForegroundColor Red
    }
}
