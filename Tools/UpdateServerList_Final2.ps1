# Final correct ServerList.bmd generator

$key = @(0xFC, 0xCF, 0xAB)

# Server list - use only public servers first
$servers = @(
    @{Code=0;  Port=55901; Name="DaNang_Pub0"},
    @{Code=1;  Port=55902; Name="DaNang_Pub1"},
    @{Code=2;  Port=55903; Name="DaNang_Pub2"},
    @{Code=3;  Port=55904; Name="DaNang_Pub3"},
    @{Code=14; Port=55919; Name="DaNang_Siege"}
)

# XOR keys derived from original analysis:
# IP: each byte XORed with 0x77 
# Port: low byte XORed with 0x1F, high byte with 0x56

# Build the records
$records = @()

# Record 0: Main server name (with 00 00 prefix)
$nameRec = New-Object byte[] 33
$nameRec[0] = 0x00
$nameRec[1] = 0x00
$nameBytes = [System.Text.Encoding]::ASCII.GetBytes("DaNang")
for ($i = 0; $i -lt $nameBytes.Length; $i++) {
    $nameRec[2 + $i] = $nameBytes[$i]
}
$records += ,$nameRec

# Build each server record
for ($i = 0; $i -lt $servers.Count; $i++) {
    $srv = $servers[$i]
    $rec = New-Object byte[] 33
    
    # Server code (bytes 0-1)
    $rec[0] = $srv.Code -band 0xFF
    $rec[1] = ($srv.Code -shr 8) -band 0xFF
    
    # Server type (byte 2) - 0x01 for normal
    $rec[2] = 0x01
    
    # Server settings (bytes 3-8)
    $rec[3] = 0x03  # Server1
    $rec[4] = 0x03  # Server2
    $rec[5] = 0x03  # Server3
    $rec[6] = 0x03  # Server4
    $rec[7] = 0x02  # Server5
    $rec[8] = 0x01  # Server6
    
    # Bytes 9-17: zeros
    
    # Bytes 18-19: 02 00
    $rec[18] = 0x02
    $rec[19] = 0x00
    
    # IP bytes 20-23: XOR with 0x77 each
    # IP = 192.168.100.96
    $rec[20] = 0xC0 -bxor 0x77  # = 0xB7
    $rec[21] = 0xA8 -bxor 0x77  # = 0xDF
    $rec[22] = 0x64 -bxor 0x77  # = 0x31
    $rec[23] = 0x60 -bxor 0x77  # = 0x35
    
    # Port bytes 24-25: XOR with 0x1F and 0x56
    $portLo = $srv.Port -band 0xFF
    $portHi = ($srv.Port -shr 8) -band 0xFF
    $rec[24] = $portLo -bxor 0x1F
    $rec[25] = $portHi -bxor 0x56
    
    # Bytes 26-27: 08 3F
    $rec[26] = 0x08
    $rec[27] = 0x3F
    
    # Server name (bytes 28-31)
    $srvNameBytes = [System.Text.Encoding]::ASCII.GetBytes($srv.Name)
    for ($j = 0; $j -lt 4 -and $j -lt $srvNameBytes.Length; $j++) {
        $rec[28 + $j] = $srvNameBytes[$j]
    }
    
    $records += ,$rec
}

# XOR encode all data
$output = New-Object System.Collections.Generic.List[byte]
for ($r = 0; $r -lt $records.Count; $r++) {
    for ($i = 0; $i -lt 33; $i++) {
        $output.Add($records[$r][$i] -bxor $key[$i % 3])
    }
}

# Save
$outPath = "C:\MU-Project\IGCFullClient\Data\Local\ServerList.bmd"
[System.IO.File]::WriteAllBytes($outPath, $output.ToArray())

Write-Host "Created ServerList.bmd with $($records.Count) records ($($output.Count) bytes)"
Write-Host "Servers:"
foreach ($srv in $servers) {
    Write-Host "  Code $($srv.Code): Port $($srv.Port) - $($srv.Name)"
}
