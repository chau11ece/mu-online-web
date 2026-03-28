FROM mcr.microsoft.com/windows/servercore:ltsc2022

# 1. Install VC++ Redist
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:\vc_redist.x64.exe /quiet /install

# 2. ODBC Driver (Required for SQLSRV)
RUN powershell -Command "Invoke-WebRequest -Uri 'https://go.microsoft.com/fwlink/?linkid=2345415&clcid=0x409' -OutFile 'C:/msodbcsql.msi' -UserAgent 'Mozilla/5.0'"
RUN msiexec /i C:\msodbcsql.msi /qn /norestart IACCEPTMSODBCSQLLICENSETERMS=YES

# 3. Download PHP 8.2.30 NTS x64 for Windows
RUN powershell -Command "Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.30-nts-Win32-vs16-x64.zip' -OutFile 'C:/php.zip' -UseBasicParsing; Expand-Archive -Path 'C:/php.zip' -DestinationPath 'C:/php'; Remove-Item 'C:/php.zip'"

# 4. Download SQLSRV + PDO_SQLSRV extensions for PHP 8.2 NTS x64
RUN powershell -Command "Invoke-WebRequest -Uri 'https://github.com/microsoft/msphpsql/releases/download/v5.12.0/Windows_5.12.0RTW.zip' -OutFile 'C:/sqlsrv.zip' -UseBasicParsing; Expand-Archive -Path 'C:/sqlsrv.zip' -DestinationPath 'C:/sqlsrv_tmp'; Get-ChildItem 'C:/sqlsrv_tmp' -Recurse | Where-Object { $_.Name -match 'php_(pdo_)?sqlsrv_82_nts_x64' } | Copy-Item -Destination 'C:/php/ext/'; Remove-Item 'C:/sqlsrv_tmp' -Recurse -Force; Remove-Item 'C:/sqlsrv.zip'"

# 5. Copy configured php.ini (only the config file — binaries come from step 3/4)
COPY php/php.ini C:/php/php.ini

# 6. Copy Web files
COPY . C:/inetpub/wwwroot/

# 7. Copy start script
COPY start.ps1 C:/start.ps1

WORKDIR C:/inetpub/wwwroot
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
  CMD powershell -Command \
    "try { $r = Invoke-WebRequest -Uri 'http://localhost/health.php' -UseBasicParsing; if ($r.StatusCode -eq 200) { exit 0 } else { exit 1 } } catch { exit 1 }"

CMD ["powershell", "-Command", "C:/start.ps1"]
