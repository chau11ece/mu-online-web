$file = 'C:\MU-Project\Start-MUServer.ps1'
$content = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)

# Replace any non-ASCII characters with plain hyphen
$content = $content -replace '[^\x00-\x7F]+', '-'

[System.IO.File]::WriteAllText($file, $content, [System.Text.Encoding]::UTF8)
Write-Host "Fixed: replaced all non-ASCII characters with plain hyphens"

# Verify parse
$errors = $null
$null = [System.Management.Automation.Language.Parser]::ParseFile($file, [ref]$null, [ref]$errors)
if ($errors.Count -eq 0) { Write-Host "Script now parses OK" }
else {
    Write-Host "Still $($errors.Count) parse error(s):"
    $errors | ForEach-Object { Write-Host "  $_" }
}
