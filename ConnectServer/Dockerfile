FROM mcr.microsoft.com/windows/servercore:ltsc2019

WORKDIR C:/ConnectServer

# Copy all files (IGCCS.ini, IGC.ConnectServer.exe, etc.)
COPY . .

# Install Visual C++ Redistributables (Required for IGCN)
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:/vc_redist.x64.exe /install /quiet /norestart

# Expose ports from your .ini
EXPOSE 44405
EXPOSE 55667/udp

# Use the correct filename
ENTRYPOINT ["IGC.ConnectServer.exe"]
