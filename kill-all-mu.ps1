# Kill ALL MU server processes by executable name (catches zombies without listening ports)
$exeNames = @('IGC.GameServer_R', 'IGC.GameServer_C', 'IGC.ConnectServer', 'IGC.DataServer')
foreach ($name in $exeNames) {
    $procs = Get-Process -Name $name -ErrorAction SilentlyContinue
    foreach ($p in $procs) {
        try {
            Stop-Process -Id $p.Id -Force -ErrorAction Stop
            Write-Host "Killed: $($p.Name) PID $($p.Id)"
        } catch {
            Write-Host "Could not kill $($p.Name) PID $($p.Id): $_"
        }
    }
}
Write-Host "Done."
