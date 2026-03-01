# Update ServerList.bmd for MU Online Client - Proper Format
# This script creates the ServerList.bmd file matching the working format

$key = @(0xFC, 0xCF, 0xAB)

# Server Configuration
$servers = @(
    @{Code=0;  Port=55901; Name="DaNang_Pub0";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=1;  Port=55902; Name="DaNang_Pub1";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=2;  Port=55903; Name="DaNang_Pub2";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=3;  Port=55904; Name="DaNang_Pub3";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=14; Port=55919; Name="DaNang_Siege";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=20; Port=55920; Name="DaNang_Prv1A";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=21; Port=55921; Name="DaNang_Prv1B";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=40; Port=55940; Name="DaNang_Prv2A";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=41; Port=55941; Name="DaNang_Prv2B";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1}
)

# IP Address (stored as bytes)
$ipBytes = @(0xC0, 0xA8, 0x64, 0x60)  # 192.168.100.96 in hex

function Get-ServerRecord {
    param($server, $index)
    
    $record = New-Object byte[] 33
    
    # Bytes 0-1: Server Code (little-endian)
    $record[0] = $server.Code -band 0xFF
    $record[1] = ($server.Code -shr 8) -band 0xFF
    
    # Bytes 2-5: Server settings (Server1-Server4)
    $record[2] = $server.Server1
    $record[3] = $server.Server2
    $record[4] = $server.Server3
    $record[5] = $server.Server4
    
    # Bytes 6-7: Server5-Server6
    $record[6] = $server.Server5
    $record[7] = $server.Server6
    
    # Byte 8: Position
    $record[8] = $index + 1
    
    # Bytes 9-17: Unknown (zeros)
    
    # Byte 18: Unknown (2)
    $record[18] = 0x02
    
    # Byte 19: Unknown (0)
    $record[19] = 0x00
    
    # Bytes 20-23: IP bytes (0xC0=192, 0xA8=168, 0x64=100, 0x60=96)
    $record[20] = $ipBytes[0]
    $record[21] = $ipBytes[1]
    $record[22] = $ipBytes[2]
    $record[23] = $ipBytes[3]
    
    # Byte 24: Port (high byte)
    $record[24] = ($server.Port -shr 8) -band 0xFF
    
    # Byte 25: Port (low byte)
    $record[25] = $server.Port -band 0xFF
    
    # Byte 26: Unknown (2)
    $record[26] = 0x02
    
    # Byte 27: Unknown (0)
    $record[27] = 0x00
    
    # Byte 28: Name length
    $record[28] = $server.Name.Length
    
    # Bytes 29-32: Server name (first 4 chars)
    $nameBytes = [System.Text.Encoding]::ASCII.GetBytes($server.Name)
    for ($i = 0; $i -lt 4 -and $i -lt $nameBytes.Length; $i++) {
        $record[29 + $i] = $nameBytes[$i]
    }
    
    return $record
}

# Create records
$records = @()

# Record 0: Server name "DaNang"
$nameRecord = New-Object byte[] 33
$nameBytes = [System.Text.Encoding]::ASCII.GetBytes("DaNang")
for ($i = 0; $i -lt $nameBytes.Length; $i++) {
    $nameRecord[$i] = $nameBytes[$i]
}
$records += ,$nameRecord

# Records 1-9: Server info
for ($i = 0; $i -lt $servers.Count; $i++) {
    $records += ,(Get-ServerRecord $servers[$i] $i)
}

# Fill remaining to 10 records (33 bytes each = 330 bytes total)
while ($records.Count -lt 10) {
    $records += ,(New-Object byte[] 33)
}

# XOR encode and save
$outputData = New-Object System.Collections.Generic.List[byte]

for ($r = 0; $r -lt $records.Count; $r++) {
    $record = $records[$r]
    for ($i = 0; $i -lt $record.Length; $i++) {
        $outputData.Add($record[$i] -bxor $key[$i % $key.Length])
    }
}

# Save to file
$outputPath = "C:\MU-Project\IGCFullClient\Data\Local\ServerList.bmd"
[System.IO.File]::WriteAllBytes($outputPath, $outputData.ToArray())

Write-Host "ServerList.bmd has been updated!"
Write-Host "Output: $outputPath"
Write-Host "Total records: $($records.Count)"
Write-Host ""
Write-Host "Servers configured:"
foreach ($server in $servers) {
    Write-Host "  - Code $($server.Code): $($server.Name) - Port $($server.Port)"
}
