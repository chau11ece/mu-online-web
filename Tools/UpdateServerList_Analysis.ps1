# Analysis script to find XOR pattern

# Original stored bytes (from decoded ServerList_Original.bmd)
$storedIP = @(0x77, 0x33, 0x32, 0x64)

# The original IP was: 119.51.100.100 = "w32d" (text), but as bytes: 119=w, 51=3, 100=d
# Let's try various IP addresses to find the pattern

Write-Host "Finding XOR pattern from original data:"
Write-Host "Stored IP bytes: $($storedIP -join ', ')"

# Try to find what IP the original was using
# The client may be using a different decoding method

# Let's try: original IP = 192.168.100.96 (0xC0, 0xA8, 0x64, 0x60)
$testIP = @(0xC0, 0xA8, 0x64, 0x60)

Write-Host "`nTesting IP 192.168.100.96:"
for ($i = 0; $i -lt 4; $i++) {
    $key = $storedIP[$i] -bxor $testIP[$i]
    Write-Host "  Position $i: stored=$($storedIP[$i]) XOR key=$('{0:X2}' -f $key) = original=$('{0:X2}' -f $testIP[$i])"
}

# Try different IP patterns
Write-Host "`nAnalysis of port encoding:"
$storedPort = @(0x1F, 0x56)  # from original
$testPort = @(0x5D, 0xDA)    # 55901 = 0xDA5D in little-endian

for ($i = 0; $i -lt 2; $i++) {
    $key = $storedPort[$i] -bxor $testPort[$i]
    Write-Host "  Position $i: stored=$($storedPort[$i]) XOR key=$('{0:X2}' -f $key) = original=$('{0:X2}' -f $testPort[$i])"
}

Write-Host "`nNow testing with correct XOR keys..."
