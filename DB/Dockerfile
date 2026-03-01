# Use the specified base image [cite: 1]
FROM mcr.microsoft.com/dotnet/framework/runtime:3.5-windowsservercore-ltsc2019

# Set PowerShell as the default shell for all subsequent RUN commands
SHELL ["powershell", "-Command", "$ErrorActionPreference = 'Stop';"]

# Create all required directories at once [cite: 1]
RUN New-Item -ItemType Directory -Path 'C:\SQLInstall', 'C:\SQLScripts', 'C:\SQLExtras', 'C:\SQLData'

# Copy local SQL scripts and extras into the image [cite: 1]
COPY ["1. Scripts", "C:/SQLScripts"]
COPY ["2. Extras", "C:/SQLExtras"]
COPY restore-databases.ps1 C:/restore-databases.ps1
COPY start.ps1 C:/start.ps1

# Download the SQL 2019 Express Advanced installer [cite: 1]
ADD https://download.microsoft.com/download/7/c/1/7c14e92e-bdcb-4f89-b7cf-93543e7112d1/SQLEXPRADV_x64_ENU.exe C:/SQLInstall/

# Install SQL Server 2019 silently [cite: 1, 2]
RUN Start-Process C:/SQLInstall/SQLEXPRADV_x64_ENU.exe -ArgumentList '/Q', '/ACTION=INSTALL', '/FEATURES=SQLENGINE', '/INSTANCENAME=MSSQLSERVER', '/SECURITYMODE=SQL', '/SAPWD=\"Abcd@1234\"', '/SQLSVCACCOUNT=\"NT AUTHORITY\System\"', '/AGTSVCACCOUNT=\"NT AUTHORITY\System\"', '/SQLSYSADMINACCOUNTS=\"BUILTIN\Administrators\"', '/IACCEPTSQLSERVERLICENSETERMS', '/TCPENABLED=1', '/UPDATEENABLED=False' -Wait

# Update SQL Server to store data in C:\SQLData for persistence [cite: 1]
RUN Set-ItemProperty -Path 'HKLM:\SOFTWARE\Microsoft\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQLServer' -Name 'DefaultData' -Value 'C:\SQLData'; \
    Set-ItemProperty -Path 'HKLM:\SOFTWARE\Microsoft\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQLServer' -Name 'DefaultLog' -Value 'C:\SQLData'

# Cleanup installer to reduce image size [cite: 1]
RUN Remove-Item -Recurse -Force C:\SQLInstall

EXPOSE 1433

# Run the start script to initialize and run SQL Server
CMD ["powershell", "-Command", "C:/start.ps1"]
