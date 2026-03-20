# Guía de Despliegue en Producción - Vincebus's FTP

Esta guía detalla los pasos para desplegar la aplicación en tu servidor con Debian Trixie y procesador Xeon L5420 utilizando Docker.

## Requisitos Previos en el Servidor
1.  **Docker & Docker Compose** instalados.
2.  **Git** para clonar el repositorio (o transferir los archivos).

## Estructura de Archivos
Asegúrate de tener los siguientes archivos en la raíz de tu proyecto:
- `docker-compose.prod.yml`
- `Dockerfile.prod`
- `.env.production` (debes crearlo basado en `.env`)

---

## Pasos para el Despliegue

### 1. Preparar el archivo de entorno
Crea un archivo `.env.production` y asegúrate de configurar:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://tu-dominio-o-ip
DB_CONNECTION=mysql
DB_HOST=vincebus-ftp-db-prod
DB_DATABASE=vincebus_ftp
DB_USERNAME=vincebus
DB_PASSWORD=una_contraseña_segura
```

### 2. Construir e Iniciar los Contenedores
Ejecuta el siguiente comando para construir la imagen optimizada (Node + PHP) e iniciar los servicios:
```bash
docker compose -f docker-compose.prod.yml up -d --build
```

### 3. Ejecutar Migraciones en Producción
Una vez que los contenedores estén corriendo, inicializa la base de datos:
```bash
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### 4. Configurar Permisos de Archivos
Para asegurar que Laravel pueda escribir en el volumen de almacenamiento:
```bash
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data storage bootstrap/cache
```

---

## Notas sobre el Hardware (Xeon L5420)
- **Optimización:** El `Dockerfile.prod` utiliza imágenes base basadas en **Alpine Linux**, que son extremadamente ligeras (aprox. 5MB base), ideales para maximizar el rendimiento de tu CPU Xeon.
- **Memoria:** Se recomienda monitorear el uso de RAM del contenedor de MySQL, ya que es el componente más demandante.

## Copias de Seguridad
Los datos de la base de datos se persisten en el volumen `mysql_prod_data`. Puedes hacer copias de seguridad de este volumen o usar `mysqldump` desde el host:
```bash
docker exec vincebus-ftp-db-prod mysqldump -u vincebus -p vincebus_ftp > backup.sql
```
