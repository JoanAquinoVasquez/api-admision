# Sistema de Admisión EPG - UNPRG (API)

API REST para el Sistema de Admisión de la Escuela de Posgrado de la Universidad Nacional Pedro Ruiz Gallo.

## Requisitos

- PHP >= 8.2
- MySQL >= 5.7
- Composer
- XAMPP (o servidor web similar)

## Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd admision-epg-api
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Configurar el archivo .env

```bash
cp .env-example .env
```

Edita el archivo `.env` y configura:

- `DB_DATABASE`: Nombre de tu base de datos
- `DB_USERNAME`: Usuario de MySQL
- `DB_PASSWORD`: Contraseña de MySQL
- `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET`: Credenciales de Google OAuth
- `JWT_SECRET`: Se generará automáticamente

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Generar clave JWT

```bash
php artisan jwt:secret
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. (Opcional) Ejecutar seeders

```bash
php artisan db:seed
```

## Ejecutar el proyecto

### Opción 1: Servidor de desarrollo Laravel

```bash
php artisan serve
```

La API estará disponible en: `http://127.0.0.1:8000/admision-epg/api`

### Opción 2: Con colas y logs (recomendado)

```bash
composer dev
```

Esto ejecutará:

- Servidor Laravel (`php artisan serve`)
- Worker de colas (`php artisan queue:listen`)
- Logs en tiempo real (`php artisan pail`)

### Opción 3: Servicios individuales

```bash
# Terminal 1: Servidor
php artisan serve

# Terminal 2: Colas
php artisan queue:work

# Terminal 3: Logs (opcional)
php artisan pail
```

## Endpoints principales

La API está disponible en el prefijo: `/admision-epg/api`

Para ver todas las rutas disponibles:

```bash
php artisan route:list --path=api
```

## Autenticación

La API utiliza JWT (JSON Web Tokens) para autenticación. Los tokens se almacenan en cookies HTTP-only.

### Login con Google

```
POST /admision-epg/api/google-login
```

### Verificar autenticación

```
GET /admision-epg/api/check-auth
```

## Configuración de CORS

Si necesitas configurar CORS para tu frontend, edita `config/cors.php`.

## Testing

```bash
php artisan test
```

## Licencia

MIT License
