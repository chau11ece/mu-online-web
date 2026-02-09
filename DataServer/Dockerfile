FROM mcr.microsoft.com/windows/servercore:ltsc2019

WORKDIR C:/DataServer

# Copy all DataServer files
COPY . .

# Install Visual C++ Redistributable (DataServer definitely needs this)
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:/vc_redist.x64.exe /install /quiet /norestart

# DataServer default port (check your .ini, usually 55960 or 55962)
EXPOSE 55960

# Start the DataServer
# Replace 'IGC.DataServer.exe' with your actual filename if different
ENTRYPOINT ["IGC.DataServer.exe"]
