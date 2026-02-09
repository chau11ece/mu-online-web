# TODO: Update Mu-Online Docker Setup for Windows VPS

## Tasks to Complete
- [x] Update docker-compose.yml: Change network to 'bridge', add env_file, PUBLIC_IP env var to services, add bind mounts and named volumes
- [x] Update DB/Dockerfile to use multi-stage build (reverted to single-stage for local installer)
- [x] Update DataServer/Dockerfile to use multi-stage build (reverted to single-stage)
- [x] Update ConnectServer/Dockerfile to use multi-stage build (reverted to single-stage)
- [x] Update GameServerRegular/Dockerfile to use multi-stage build (reverted to single-stage)
- [x] Update GameServerSiege/Dockerfile to use multi-stage build (reverted to single-stage)
- [x] Update Web/Dockerfile to use multi-stage build (reverted to single-stage)
- [x] Test docker-compose up and verify services start correctly
- [x] Update .env with actual VPS public IP
