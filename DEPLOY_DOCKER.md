# Deploy Docker de Teste

## Subir o ambiente

```powershell
docker compose up --build -d
```

## Portas padrao na VPS

- Frontend: `8081`
- API: `8000` (bind apenas em `127.0.0.1`)
- MySQL: `3307` (bind apenas em `127.0.0.1`)

Isso evita conflito com o `8080` que ja esta ocupado na VPS e reduz exposicao desnecessaria de API e banco.

## URLs

- Frontend: `http://localhost:8081`
- API local no servidor: `http://127.0.0.1:8000`
- MySQL: `localhost:3307`

## Variaveis opcionais para producao

Se for acessar por dominio ou IP da VPS, defina antes de subir:

```powershell
$env:FABRICA_FRONTEND_PORT="8081"
$env:FABRICA_API_PORT="8000"
$env:FABRICA_DB_PORT="3307"
$env:APP_URL="http://SEU_IP_OU_DOMINIO:8081"
$env:FRONTEND_URL="http://SEU_IP_OU_DOMINIO:8081"
$env:CORS_ALLOWED_ORIGINS="http://SEU_IP_OU_DOMINIO:8081"
$env:APP_DEBUG="false"
docker compose up --build -d
```

No Linux/bash:

```bash
export FABRICA_FRONTEND_PORT=8081
export FABRICA_API_PORT=8000
export FABRICA_DB_PORT=3307
export APP_URL=http://SEU_IP_OU_DOMINIO:8081
export FRONTEND_URL=http://SEU_IP_OU_DOMINIO:8081
export CORS_ALLOWED_ORIGINS=http://SEU_IP_OU_DOMINIO:8081
export APP_DEBUG=false
docker compose up --build -d
```

## Credenciais padrao

### Banco

- Database: `erp_fabrica_api`
- User: `fabrica`
- Password: `fabrica`
- Root password: `root`

### Usuario de teste ADM

- Email: `admin@fabrica.local`
- Senha: `password`

## Comandos uteis

```powershell
docker compose logs -f
docker compose down
docker compose down -v
docker compose exec api php artisan migrate:status
docker compose exec api php artisan db:seed --class=TestUsersSeeder
```
