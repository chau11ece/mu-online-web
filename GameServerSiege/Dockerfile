FROM mcr.microsoft.com/windows/servercore:ltsc2019

WORKDIR /mu/gameserver

# Install Visual C++ Redistributable
ADD https://aka.ms/vs/17/release/vc_redist.x64.exe C:/vc_redist.x64.exe
RUN C:/vc_redist.x64.exe /install /quiet /norestart

COPY . .

# GameServer Port
EXPOSE 55919

CMD ["IGC.GameServer_C.exe"]
