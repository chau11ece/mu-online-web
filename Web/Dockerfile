FROM mcr.microsoft.com/windows/servercore:ltsc2022

# 1. Install VC++ Redist
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:\vc_redist.x64.exe /quiet /install

# 2. ODBC Driver (Required for SQLSRV)
RUN powershell -Command "Invoke-WebRequest -Uri 'https://go.microsoft.com/fwlink/?linkid=2345415&clcid=0x409' -OutFile 'C:/msodbcsql.msi' -UserAgent 'Mozilla/5.0'"
RUN msiexec /i C:\msodbcsql.msi /qn /norestart IACCEPTMSODBCSQLLICENSETERMS=YES

# 3. Copy your local PHP (which now has the DLLs and the clean php.ini)
COPY php/ C:/php/

# 4. Copy Web files
COPY . C:/inetpub/wwwroot/

# 5. Copy start script
COPY start.ps1 C:/start.ps1

WORKDIR C:/inetpub/wwwroot
EXPOSE 80

# ── EXERCISE 1: Health check ──────────────────────────────────────────────────
# Docker will call this every 30s
# --start-period=60s gives IIS time to fully start before first check
# If it fails 3 times in a row → container is marked "unhealthy"
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
  CMD powershell -Command \
    "try { $r = Invoke-WebRequest -Uri 'http://localhost/health.php' -UseBasicParsing; if ($r.StatusCode -eq 200) { exit 0 } else { exit 1 } } catch { exit 1 }"

CMD ["powershell", "-Command", "C:/start.ps1"]