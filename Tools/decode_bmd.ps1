$file = $args[0]
$key = @(0xFC, 0xCF, 0xAB)
$data = [System.IO.File]::ReadAllBytes($file)
$decoded = New-Object byte[] $data.Length
for ($i = 0; $i -lt $data.Length; $i++) {
    $decoded[$i] = $data[$i] -bxor $key[$i % $key.Length]
}
# Print records of 33 bytes
for ($r = 0; $r -lt [math]::Ceiling($decoded.Length / 33); $r++) {
    $start = $r * 33
    $end = [math]::Min($start + 33, $decoded.Length)
    $chunk = $decoded[$start..($end-1)]
    $hex = ($chunk | ForEach-Object { '{0:X2}' -f $_ }) -join ' '
    $ascii = ($chunk | ForEach-Object { if ($_ -ge 32 -and $_ -lt 127) { [char]$_ } else { '.' } }) -join ''
    Write-Host "Record $r : $hex"
    Write-Host "  ASCII :  $ascii"
    Write-Host ""
}
