# Update ServerList.bmd for MU Online Client
# This script creates the ServerList.bmd file with the new server configuration

$key = @(0xFC, 0xCF, 0xAB)

# Server Configuration - Update these values
$serverName = "DaNang"  # Max 20 characters
$servers = @(
    @{Code=0;  IP="192.168.100.96"; Port=55901; Name="DaNang_Pub0";   Type="Normal"},
    @{Code=1;  IP="192.168.100.96"; Port=55902; Name="DaNang_Pub1";   Type="Normal"},
    @{Code=2;  IP="192.168.100.96"; Port=55903; Name="DaNang_Pub2";   Type="VIP"},
    @{Code=3;  IP="192.168.100.96"; Port=55904; Name="DaNang_Pub3";   Type="NonPvP"},
    @{Code=14; IP="192.168.100.96"; Port=55919; Name="DaNang_Siege";  Type="Siege"},
    @{Code=20; IP="192.168.100.96"; Port=55920; Name="DaNang_Prv1A";   Type="Normal"},
    @{Code=21; IP="192.168.100.96"; Port=55921; Name="DaNang_Prv1B";   Type="Normal"},
    @{Code=40; IP="192.168.100.96"; Port=55940; Name="DaNang_Prv2A";   Type="Normal"},
    @{Code=41; IP="192.168.100.96"; Port=55941; Name="DaNang_Prv2B";   Type="Normal"}
)

function Convert-IPToBytes {
    param([string]$ip)
    $parts = $ip.Split('.')
    $result = ""
    foreach ($part in $parts) {
        $result += [char][byte]$part
    }
    return $result
}

function Convert-PortToBytes {
    param([int]$port)
    # Port is stored as little-endian 16-bit
    $lo = $port -band 0xFF
    $hi = ($port -shr 8) -band 0xFF
    return [char]$lo + [char]$hi
}

function Get-RecordBytes {
    param($server, $index)
    
    $record = New-Object byte[] 33
    
    # Byte 0-1: Server Code (16-bit little-endian)
    $record[0] = $server.Code -band 0xFF
    $record[1] = ($server.Code -shr 8) -band 0xFF
    
    # Bytes 2-5: Some flags/settings (copy from working config as reference)
    # Based on the original data pattern
    if ($server.Type -eq "VIP") {
        $record[2] = 0x02
    } elseif ($server.Type -eq "NonPvP") {
        $record[3] = 0x01  # Non-PvP flag
    }
    
    # Bytes 6-9: More settings
    $record[6] = 0x01
    
    # Bytes 10-20: IP Address (15 chars max, null-terminated)
    $ipBytes = [System.Text.Encoding]::ASCII.GetBytes($server.IP)
    for ($i = 0; $i -lt $ipBytes.Length -and $i -lt 15; $i++) {
        $record[10 + $i] = $ipBytes[$i]
    }
    $record[10 + $ipBytes.Length] = 0  # Null terminator
    
    # Bytes 21-22: Port (16-bit little-endian)
    $portLo = $server.Port -band 0xFF
    $portHi = ($server.Port -shr 8) -band 0xFF
    $record[21] = $portLo
    $record[22] = $portHi
    
    # Bytes 23-24: More settings
    $record[23] = 0x02
    
    # Byte 25: Server name length (?)
    $record[25] = $server.Name.Length
    
    # Bytes 26-32: Server name (7 chars max in this section)
    $nameBytes = [System.Text.Encoding]::ASCII.GetBytes($server.Name)
    for ($i = 0; $i -lt $nameBytes.Length -and $i -lt 7; $i++) {
        $record[26 + $i] = $nameBytes[$i]
    }
    
    return $record
}

# Create records
$records = @()

# Record 0: Server name (20 bytes + null = 21, but we need 33 bytes)
$nameRecord = New-Object byte[] 33
$nameBytes = [System.Text.Encoding]::ASCII.GetBytes($serverName)
for ($i = 0; $i -lt $nameBytes.Length; $i++) {
    $nameRecord[$i] = $nameBytes[$i]
}
# Rest stays 0
$records += ,$nameRecord

# Records 1-N: Server info
foreach ($server in $servers) {
    $records += ,(Get-RecordBytes $server)
}

# Fill remaining records with zeros (if needed for compatibility)
while ($records.Count -lt 5) {
    $records += ,(New-Object byte[] 33)
}

# XOR encode and save
$outputData = New-Object System.Collections.Generic.List[byte]

foreach ($record in $records) {
    for ($i = 0; $i -lt $record.Length; $i++) {
        $outputData.Add($record[$i] -bxor $key[$i % $key.Length])
    }
}

# Save to file
$outputPath = "C:\MU-Project\IGCFullClient\Data\Local\ServerList.bmd"
[System.IO.File]::WriteAllBytes($outputPath, $outputData.ToArray())

Write-Host "ServerList.bmd has been updated successfully!"
Write-Host "Output: $outputPath"
Write-Host "Servers configured:"
foreach ($server in $servers) {
    Write-Host "  - $($server.Name) (Code: $($server.Code), $($server.IP):$($server.Port))"
}
