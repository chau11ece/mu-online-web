Get-Process | Where-Object { $_.Name -like 'IGC*' } | Select-Object Id, Name, @{N='StartTime';E={$_.StartTime.ToString('HH:mm:ss')}} | Format-Table -AutoSize
