FROM mcr.microsoft.com/windows/servercore:ltsc2019

WORKDIR /mu/gameserver

# Install Visual C++ Redistributable (GameServers are very picky)
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:/vc_redist.x64.exe /install /quiet /norestart

# Copy only the files shown in your list
COPY . .

# GameServer Port
EXPOSE 55901

# Start the server
ENTRYPOINT ["IGC.GameServer_R.exe"]
