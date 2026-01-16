---
trigger: always_on
---

# Infrastructure & Docker Architecture

## Contexto

Estas reglas definen cómo se debe construir, configurar y orquestar la infraestructura del proyecto utilizando Docker y Traefik. El objetivo es garantizar la consistencia de permisos entre el host y los contenedores, y mantener una arquitectura limpia de microservicios.

## 1. Estructura de Directorios

- **Raíz:** Toda la configuración de infraestructura reside estrictamente en la carpeta `docker/`.
- **Servicios:** Cada servicio tiene su propia subcarpeta (ej: `docker/php/`, `docker/nginx/`, `docker/mysql/`).
- **Contenido:** Dentro de cada subcarpeta deben estar el `Dockerfile` y los archivos de configuración necesarios (`nginx.conf`, `php.ini`, etc.).

## 2. Estándar para Dockerfiles (Gestión de Permisos)

Para evitar problemas de permisos en los volúmenes montados, es **OBLIGATORIO** sincronizar el usuario del contenedor con el usuario del host, excepto en los contenedores de bases de datos, ahí no es necesario.

- **UID/GID:** El usuario principal del servicio (ej: `www-data`) debe tener **UID 1000** y **GID 1000**.
- **Comandos de Ajuste:** Usa `usermod` y `groupmod` en el Dockerfile, no elimines usuarios, revisa que usuario necesita cada servicio y usa el correspondiente (ej: `www-data`).
- **Usuario Final:** El contenedor debe ejecutarse bajo `USER 1000`.
- **Imagen de Docker:** Las imagenes alpine normalmente dan problemas, intenta usar alguna basada en otro sistema operativo, como por ejemplo, Debian.

**Plantilla Base para Dockerfile (PHP):**

```dockerfile
FROM php:8.2-fpm

# Instalación de dependencias...

# Ajuste crítico de permisos para coincidir con Host (UID 1000)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Configurar home directory para www-data
RUN usermod -d /home/dev www-data

# Configuración de directorios de trabajo
RUN mkdir -p /home/dev/code /home/dev/.composer
RUN chown -R www-data:www-data /home/dev/

# Configurar variables de entorno para Composer
ENV COMPOSER_HOME=/home/dev/.composer
ENV COMPOSER_CACHE_DIR=/home/dev/.composer/cache

# Configuración final
USER 1000
WORKDIR /home/dev/code
```

## 3. Nginx: Configuración Correcta

Nginx requiere una configuración en dos niveles para un funcionamiento correcto con usuarios no-root.

### 3.1. Archivo Principal (nginx.conf)

Directivas globales como `user`, `pid`, `error_log`, y `events` deben estar en `/etc/nginx/nginx.conf`.

**CRÍTICO:** La directiva `pid` SOLO puede estar en el archivo principal de nginx, NO en archivos de `/etc/nginx/conf.d/`.

**Plantilla Base para nginx.conf (nginx-main.conf):**

```nginx
user nginx;
worker_processes auto;

# PID file en directorio escribible
pid /home/dev/nginx/nginx.pid;

error_log /dev/stderr notice;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    sendfile on;
    tcp_nopush on;
    keepalive_timeout 65;
    gzip on;

    include /etc/nginx/conf.d/*.conf;
}
```

### 3.2. Configuración del Servidor (server blocks)

Los bloques `server` deben estar en archivos separados dentro de `/etc/nginx/conf.d/`.

**NO INCLUYAS** estas directivas en los archivos de configuración del servidor:

- ❌ `pid` - Solo en nginx.conf principal
- ❌ `user` - Solo en nginx.conf principal
- ❌ `events` - Solo en nginx.conf principal
- ❌ `http` - Solo en nginx.conf principal

**Plantilla Base para configuración de servidor:**

```nginx
server {
    listen 80;
    server_name example.local;
    root /home/dev/code/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    # Logs a stdout/stderr (accesibles desde docker logs)
    error_log stderr;
    access_log /dev/stdout;
}
```

### 3.3. Dockerfile de Nginx

**Plantilla Base para Dockerfile:**

```dockerfile
FROM nginx:mainline

# Copiar configuración principal de Nginx
COPY nginx-main.conf /etc/nginx/nginx.conf

# Copiar configuración del servidor
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Ajuste crítico de permisos para coincidir con Host (UID 1000)
RUN usermod -u 1000 nginx && groupmod -g 1000 nginx

# Configuración de directorios de trabajo
RUN mkdir -p /home/dev/code /home/dev/nginx
RUN chown -R nginx:nginx /home/dev/ /var/cache/nginx

# Configurar directorio PID para Nginx
ENV NGINX_PID_DIR=/home/dev/nginx

# Configuración final
USER 1000
WORKDIR /home/dev/code
```

**Archivos requeridos para Nginx:**

1. `nginx-main.conf` - Configuración principal con directivas globales
2. `nginx.conf` - Configuración del servidor (server blocks)
3. `Dockerfile` - Que copie ambos archivos a las ubicaciones correctas

## 4. Docker Compose & Traefik

El archivo `docker-compose.yml` en la raíz debe seguir estas normas para la orquestación y el enrutamiento.

- **Naming Convention:** Los servicios deben llamarse `<proyecto>_<servicio>` (ej: `proyecto_php-fpm`).
- **Red:** Debe existir una red `default` definida como **external** con nombre `traefik`.
- **Puertos:** NO exponer puertos `80:80` en el host. El tráfico entra exclusivamente por Traefik.
- **Traefik Labels:** Configura routers HTTP y HTTPS con TLS habilitado.

**Plantilla Base para docker-compose.yml:**

```yaml
services:
  # PHP Service
  <app>_php-fpm:
    build: ./docker/php
    volumes:
      - .:/home/dev/code
    depends_on:
      - <app>_db

  # Nginx Service (Traefik Enabled)
  <app>_nginx:
    build: ./docker/nginx
    volumes:
      - .:/home/dev/code
    depends_on:
      - <app>_php-fpm
    labels:
      - "traefik.enable=true"
      # HTTP Router
      - "traefik.http.routers.<app>_http.rule=Host(`<dominio>.local`)"
      - "traefik.http.routers.<app>_http.entrypoints=http"
      # HTTPS Router
      - "traefik.http.routers.<app>_https.rule=Host(`<dominio>.local`)"
      - "traefik.http.routers.<app>_https.entrypoints=https"
      - "traefik.http.routers.<app>_https.tls=true"
      # Service Port (Puerto interno del contenedor)
      - "traefik.http.services.<app>_http.loadbalancer.server.port=80"

  # Database Service
  <app>_db:
    build: ./docker/mysql
    restart: unless-stopped
    environment:
      - MYSQL_ROOT_PASSWORD=secret
    volumes:
      - db_volume:/var/lib/mysql

volumes:
  db_volume:

networks:
  default:
    external: true
    name: traefik
```

## 5. Ejecución de Comandos

Nunca se deben ejecutar comandos de entorno (PHP, Node, Composer) directamente en la máquina host.

- **Regla:** Todo comando se lanza a través de `docker compose exec`.
- **Ejemplos:**
  - `docker compose exec <app>_php-fpm composer install`
  - `docker compose exec <app>_php-fpm php bin/console cache:clear`
  - `docker compose exec <app>_node npm install`

## 6. Problemas Comunes y Soluciones

### 6.1. Error: "pid" directive is not allowed here

**Causa:** La directiva `pid` está en un archivo dentro de `/etc/nginx/conf.d/`

**Solución:**

- Mover la directiva `pid` al archivo principal `/etc/nginx/nginx.conf`
- Crear archivo `nginx-main.conf` con todas las directivas globales
- Actualizar el Dockerfile para copiar ambos archivos

### 6.2. Permisos de Composer Cache

**Causa:** El home directory de `www-data` no es escribible

**Solución:**

```dockerfile
RUN usermod -d /home/dev www-data
ENV COMPOSER_HOME=/home/dev/.composer
ENV COMPOSER_CACHE_DIR=/home/dev/.composer/cache
```

### 6.3. Logs de Nginx No Accesibles

**Causa:** El usuario no puede escribir en `/var/log/nginx/`

**Solución:**

```nginx
error_log stderr;
access_log /dev/stdout;
```

## Checklist de Verificación

Antes de hacer build de los contenedores, verifica:

- [ ] Todos los usuarios tienen UID/GID 1000
- [ ] El directorio `/home/dev` existe y tiene permisos correctos
- [ ] Nginx tiene dos archivos: `nginx-main.conf` y `nginx.conf`
- [ ] La directiva `pid` está SOLO en `nginx-main.conf`
- [ ] Los logs apuntan a stdout/stderr
- [ ] Las variables de entorno de Composer están configuradas
- [ ] Docker Compose usa la red externa `traefik`
- [ ] Los servicios tienen labels de Traefik correctos

## Mas documentacion.

Puedes leer en el directorio docs en la raiz del proyecto para ampliar informacion.