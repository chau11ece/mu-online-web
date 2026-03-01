$muPorts = @(55901,55902,55903,55904,55919,55920,55921,55940,55941,44405,56960)
$lines = netstat -ano | Select-String "LISTENING"
$killed = @{}
foreach ($line in $lines) {
    if ($line -match ":(\d+)\s+\S+\s+LISTENING\s+(\d+)") {
        $port = [int]$Matches[1]; $pid2 = [int]$Matches[2]
        if ($muPorts -contains $port -and -not $killed.ContainsKey($pid2)) {
            $killed[$pid2] = $port
            try { Stop-Process -Id $pid2 -Force -EA Stop; Write-Host "Stopped PID $pid2 on port $port" }
            catch { Write-Host "Could not stop PID $pid2 : $_" }
        }
    }
}
if ($killed.Count -eq 0) { Write-Host "No MU processes found running" }
else { Write-Host "Stopped $($killed.Count) processes" }
