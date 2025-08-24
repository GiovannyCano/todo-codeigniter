# ✅ Todo App (CodeIgniter 4 + Docker)

Bienvenidos a mi solución técnica para este desafío, espero satisfacer sus espectativas :)

Esta es una aplicación CRUD de tareas con **CodeIgniter 4**, **NGINX**, **PHP-FPM** y **MariaDB** lista para correr con **Docker Compose**.  
Incluye **frontend** (HTML/CSS/JS) servido en el mismo puerto, **seed automático idempotente**, y **suite de tests PHPUnit** (unit + feature).

## Stack
- PHP 8.3 (FPM) + Composer
- NGINX 1.27
- MariaDB 11
- CodeIgniter 4 (AppStarter)
- PHPUnit 10

## Requisitos
- Docker + Docker Compose
- Git

> Apple Silicon (M1/M2): imágenes multi-arch (no necesitas flags especiales).  
> Windows: recomienda PowerShell o Git Bash.

---

## 🚀 Quick start (plug & play)

> El contenedor **app** trae un *entrypoint* que:
> - Instala CodeIgniter si `./app` está vacío.  
> - Ejecuta `composer install` si falta `vendor/`.  
> - Crea `.env` desde `env` y setea `CI_ENVIRONMENT=development`.  
> - Corre **migraciones** (`MIGRATE_ON_BOOT=1`).  
> - Corre **seed idempotente** si la tabla está vacía (`SEED_ON_BOOT=1`).

```bash
# Clonar
git clone https://github.com/GiovannyCano/todo-codeigniter.git
cd todo-codeigniter

# Levantar todo
docker compose up -d --build

```

App: **http://localhost:8080**

---

## 🔁 Reset & boot desde cero (Win/macOS/Linux)

```bash
# Vaciar ./app para forzar autoinstalación
# Windows (PowerShell):  Remove-Item -Recurse -Force .pp\* -ErrorAction SilentlyContinue
# macOS/Linux:          rm -rf ./app/*

docker compose down -v --remove-orphans
docker compose build --no-cache app
docker compose up -d --build
docker compose logs -f app
```

---

## 📚 Endpoints (REST)

- `GET    /tasks` — lista
- `GET    /tasks/{id}` — detalle
- `POST   /tasks` — crear `{ title, completed }`
- `PUT    /tasks/{id}` — actualizar parcial/total
- `PATCH  /tasks/{id}` — idem
- `DELETE /tasks/{id}` — eliminar

### cURL de ejemplo (sin `jq`, multiplataforma)

#### Windows (PowerShell)
> Usa **`curl.exe`** (en PowerShell `curl` es alias de `Invoke-WebRequest`).

```powershell
# GET lista
curl.exe -s http://localhost:8080/tasks

# GET lista 1 item
curl.exe -s http://localhost:8080/tasks/1

# POST crear
curl.exe -s -X POST http://localhost:8080/tasks `
  -H "Content-Type: application/json" `
  -d '{"title":"Nueva tarea","completed":0}'

# PUT actualizar
curl.exe -s -X PUT http://localhost:8080/tasks/1 `
  -H "Content-Type: application/json" `
  -d '{"title":"Editada","completed":1}'

# DELETE eliminar
curl.exe -s -X DELETE http://localhost:8080/tasks/1

# Ver SOLO el status code (sin cuerpo)
curl.exe -s -o NUL -w "%{http_code}`n" http://localhost:8080/tasks/1
```

**Pretty JSON sin instalar nada (PowerShell nativo):**
```powershell
irm http://localhost:8080/tasks | ConvertTo-Json -Depth 10
```

#### macOS / Linux
```bash
# GET lista
curl -s http://localhost:8080/tasks

# GET lista 1 item
curl -s http://localhost:8080/tasks/1

# POST crear
curl -s -X POST http://localhost:8080/tasks   -H "Content-Type: application/json"   -d '{"title":"Nueva tarea","completed":0}'

# PUT actualizar
curl -s -X PUT http://localhost:8080/tasks/1   -H "Content-Type: application/json"   -d '{"title":"Editada","completed":1}'

# DELETE eliminar
curl -s -X DELETE http://localhost:8080/tasks/1

# Ver SOLO el status code (sin cuerpo)
curl -s -o /dev/null -w "%{http_code}
" http://localhost:8080/tasks/1
```

> Tip: también puedes abrir **http://localhost:8080/tasks** en el navegador; la mayoría formatea el JSON automáticamente.

---

## 🖥️ Frontend

- Ruta: `/` (controlador `Todo::index`, vista `app/Views/todo.php`).
- La vista usa **AJAX** con `fetch` al **mismo origen**:
  - `fetch('/tasks')` (GET)
  - `fetch('/tasks', { method:'POST', ... })`
  - `fetch('/tasks/{id}', { method:'PUT', ... })`
  - `fetch('/tasks/{id}', { method:'DELETE' })`

---

## 🧪 Tests

Ejecutar suite PHPUnit:

```bash
docker compose exec app vendor/bin/phpunit --colors=always
```

Tests incluidos (`tests/`):

- **Unit**
  - `TaskModelCrudTest` — inserción/lectura, update, delete, batch.
  - `TaskModelValidationTest` — límites y validaciones.
  - `TaskModelTest` — mixtos + seeder + error con payload vacío.
- **Feature**
  - `TasksEndpointTest` — CRUD end-to-end con JSON.
  - `TasksEndpointEdgeCasesTest` — felices y fallas (404, validación, PATCH, dobles deletes).

> Si ves “No code coverage driver available”, es un warning (puedes habilitar **pcov** o **xdebug** si necesitas cobertura).

---

## 📂 Estructura

```
.
├─ app/
│  ├─ tests/
├─ docker/
│  ├─ app/
│  │  └─ entrypoint.sh
│  └─ nginx/
│     └─ default.conf
├─ docker-compose.yml
├─ Dockerfile
└─ README.md
```

---

## 📜 Licencia
MIT
