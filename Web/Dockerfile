FROM mcr.microsoft.com/windows/servercore:ltsc2019

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

WORKDIR C:/inetpub/wwwroot
EXPOSE 80
CMD ["C:\\php\\php.exe", "-c", "C:\\php\\php.ini", "-S", "0.0.0.0:80", "-t", "C:\\inetpub\\wwwroot"]
