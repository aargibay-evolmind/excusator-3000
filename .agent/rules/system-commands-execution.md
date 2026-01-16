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

Para evitar problemas de permisos en los volúmenes montados, es **OBLIGATORIO** sincronizar el usuario del contenedor con el usuario del host.

- **UID/GID:** El usuario principal del servicio (ej: `www-data`) debe tener **UID 1000** y **GID 1000**.
- **Comandos de Ajuste:** Usa `usermod` y `groupmod` en el Dockerfile para ajustar el usuario existente.
- **Usuario Final:** El contenedor debe ejecutarse bajo `USER 1000`.

**Plantilla Base para Dockerfile:**

```dockerfile
FROM <imagen_base>

# Instalación de dependencias...

# Ajuste crítico de permisos para coincidir con Host (UID 1000)
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Configuración de directorios de trabajo
RUN mkdir -p /home/dev/code
RUN chown -R www-data:www-data /home/dev/

# Configuración final
USER 1000
WORKDIR /home/dev/code
```

## 3. Docker Compose & Traefik

El archivo `docker-compose.yml` en la raíz debe seguir estas normas para la orquestación y el enrutamiento.

- **Naming Convention:** Los servicios deben llamarse `<proyecto>_<servicio>` (ej: `php-fpm`).
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

## 4. Ejecución de Comandos

Nunca se deben ejecutar comandos de entorno (PHP, Node, Composer) directamente en la máquina host.

- **Regla:** Todo comando se lanza a través de `docker compose exec`.
- **Ejemplos:**
  - `docker compose exec <app> composer install`
  - `docker compose exec <app> php`
  - `docker compose exec <app> npm install`
