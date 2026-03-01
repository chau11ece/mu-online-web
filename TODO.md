# TODO: Containerize MU Services Separately on Remote VPS (192.168.1.251) - Fixed Issues

## Steps to Complete:
- [ ] Clean up existing containers: docker stop $(docker ps -aq); docker rm $(docker ps -aq); docker volume rm mu-sqldata
- [ ] Build and run DB (SQL Server) container: docker build -t mu-db ./DB && docker run -d --name mu-db -p 1433:1433 -v mu-sqldata:C:/SQLData -e PUBLIC_IP=192.168.1.251 mu-db
- [ ] Build and run DataServer container: docker build -t mu-ds ./DataServer && docker run -d --name mu-ds -p 55960:55960 -v ./Data:C:/Data -v ./IGCData:C:/IGCData -e PUBLIC_IP=192.168.1.251 mu-ds
- [ ] Build and run ConnectServer container: docker build -t mu-cs ./ConnectServer && docker run -d --name mu-cs -p 44405:44405 -p 55667:55667/udp -e PUBLIC_IP=192.168.1.251 mu-cs
- [ ] Build and run Web container (stop conflicting service on port 80 first): net stop http /y; docker build -t mu-web ./Web && docker run -d --name mu-web -p 80:80 -e PUBLIC_IP=192.168.1.251 mu-web
- [ ] Build and run GameServerRegular container: docker build -t mu-gs-regular ./GameServerRegular && docker run -d --name mu-gs-regular -e PUBLIC_IP=192.168.1.251 mu-gs-regular
- [ ] Build and run GameServerSiege container: docker build -t mu-gs-siege ./GameServerSiege && docker run -d --name mu-gs-siege -e PUBLIC_IP=192.168.1.251 mu-gs-siege
- [ ] Check logs for issues: docker logs mu-db; docker logs mu-web; etc.
- [ ] Test connectivity: SQL at 192.168.1.251:1433, Web at 192.168.1.251:80.
- [ ] If DB still fails, check SQL Server installation in Dockerfile or run interactively: docker run -it --rm mu-db powershell
