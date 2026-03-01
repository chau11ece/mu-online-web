FROM mcr.microsoft.com/windows/servercore:ltsc2019

WORKDIR C:/DataServer

# Copy all DataServer files
COPY . .

# Install Visual C++ Redistributable (DataServer definitely needs this)
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:/vc_redist.x64.exe /install /quiet /norestart

# Copy startup script
COPY start.ps1 C:/DataServer/start.ps1

# DataServer default port (check your .ini, usually 55960 or 55962)
EXPOSE 55960

# Start via wrapper so SQL_SERVER env var can patch IGCDS.ini before launch
ENTRYPOINT ["powershell", "-File", "C:/DataServer/start.ps1"]
