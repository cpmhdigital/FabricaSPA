# Deploy Docker de Teste

## Subir o ambiente

```powershell
docker compose up --build -d
```

## URLs

- Frontend: `http://localhost:8080`
- API: `http://localhost:8000`
- MySQL: `localhost:3307`

## Credenciais padrão

### Banco

- Database: `erp_fabrica_api`
- User: `fabrica`
- Password: `fabrica`
- Root password: `root`

### Usuário de teste ADM

- Email: `admin@fabrica.local`
- Senha: `password`

## Comandos úteis

```powershell
docker compose logs -f
docker compose down
docker compose down -v
docker compose exec api php artisan migrate:status
docker compose exec api php artisan db:seed --class=TestUsersSeeder
```
