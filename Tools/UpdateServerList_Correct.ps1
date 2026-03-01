# Update ServerList.bmd for MU Online Client - CORRECT FORMAT
# This script creates the ServerList.bmd file matching the original working format

$key = @(0xFC, 0xCF, 0xAB)

# XOR key for IP bytes - derived from analysis
$ipXorKey = @(0x77, 0x66, 0x55, 0x44)  # For IP bytes

# XOR key for port bytes - derived from analysis  
$portXorKeyLo = 0x42  # For low byte
$portXorKeyHi = 0x8C  # For high byte

# Server Configuration - matching the working format
$servers = @(
    @{Code=0;  Port=55901; Name="DaNang_Pub0";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=1;  Port=55902; Name="DaNang_Pub1";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=2;  Port=55903; Name="DaNang_Pub2";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=3;  Port=55904; Name="DaNang_Pub3";   Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1},
    @{Code=14; Port=55919; Name="DaNang_Siege";  Server1=3; Server2=3; Server3=3; Server4=3; Server5=2; Server6=1}
)

function Get-XorIP {
    param([string]$ip)
    $parts = $ip.Split('.')
    $result = @()
    for ($i = 0; $i -lt 4; $i++) {
        $byte = [int]$parts[$i]
        # XOR with 0x77 for first 2 bytes, 0x55 for others
        if ($i -lt 2) {
            $xorByte = $byte -bxor 0x77
        } else {
            $xorByte = $byte -bxor 0x55
        }
        $result += $xorByte
    }
    return $result
}

function Get-XorPort {
    param([int]$port)
    $lo = $port -band 0xFF
    $hi = ($port -shr 8) -band 0xFF
    # XOR with different keys
    $loXor = $lo -bxor 0x1F
    $hiXor = $hi -bxor 0x56
    return @($loXor, $hiXor)
}

function Get-ServerRecord {
    param($server, $index)
    
    $record = New-Object byte[] 33
    
    # Bytes 0-1: Server Code (little-endian)
    $code = $server.Code
    $record[0] = $code -band 0xFF
    $record[1] = ($code -shr 8) -band 0xFF
    
    # Bytes 2: Server type (01 for normal)
    $record[2] = 0x01
    
    # Bytes 3-6: Server settings
    $record[3] = $server.Server1
    $record[4] = $server.Server2
    $record[5] = $server.Server3
    $record[6] = $server.Server4
    
    # Bytes 7-8: Server5-Server6
    $record[7] = $server.Server5
    $record[8] = $server.Server6
    
    # Bytes 9-17: Unknown (zeros)
    
    # Byte 18: Unknown (2)
    $record[18] = 0x02
    
    # Byte 19: Unknown (0)
    $record[19] = 0x00
    
    # Bytes 20-23: IP (XOR encoded)
    $ipXored = Get-XorIP -ip "192.168.100.96"
    $record[20] = $ipXored[0]
    $record[21] = $ipXored[1]
    $record[22] = $ipXored[2]
    $record[23] = $ipXored[3]
    
    # Bytes 24-25: Port (XOR encoded)
    $portXored = Get-XorPort -port $server.Port
    $record[24] = $portXored[0]
    $record[25] = $portXored[1]
    
    # Bytes 26-27: Unknown
    $record[26] = 0x08
    $record[27] = 0x3F
    
    # Bytes 28-31: Server name (partial, first chars)
    $nameBytes = [System.Text.Encoding]::ASCII.GetBytes($server.Name)
    for ($i = 0; $i -lt 4 -and $i -lt $nameBytes.Length; $i++) {
        $record[28 + $i] = $nameBytes[$i]
    }
    
    # Byte 32: Unknown
    $record[32] = 0x00
    
    return $record
}

# Create records
$records = @()

# Record 0: Server name - starts with 00 00 then name
$nameRecord = New-Object byte[] 33
$nameRecord[0] = 0x00
$nameRecord[1] = 0x00
$nameBytes = [System.Text.Encoding]::ASCII.GetBytes("DaNang")
for ($i = 0; $i -lt $nameBytes.Length; $i++) {
    $nameRecord[2 + $i] = $nameBytes[$i]
}
$records += ,$nameRecord

# Records 1-5: Server info (5 servers)
for ($i = 0; $i -lt $servers.Count; $i++) {
    $records += ,(Get-ServerRecord $servers[$i] $i)
}

# Fill remaining records if needed (for 165 bytes = 5 records)
while ($records.Count -lt 5) {
    $records += ,(New-Object byte[] 33)
}

# XOR encode each byte with the main key
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
Write-Host "Total size: $($outputData.Count) bytes"
Write-Host ""
Write-Host "Servers configured:"
foreach ($server in $servers) {
    Write-Host "  - Code $($server.Code): $($server.Name) - Port $($server.Port)"
}
