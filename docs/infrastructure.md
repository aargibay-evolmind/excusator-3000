# Excusator 3000 - Documentación de Infraestructura

## Objetivo

Este documento detalla todos los cambios de infraestructura necesarios para servir correctamente la aplicación Vue 3 + Vite en el navegador a través de Nginx y Docker.

---

## Problema Inicial

Al iniciar el desarrollo, el frontend mostraba contenido incorrecto (página de bienvenida de Vue en lugar de la aplicación) debido a una configuración inadecuada de la infraestructura.

### Causas Identificadas

1. **Nginx servía archivos estáticos obsoletos** desde `/frontend/dist`
2. **Falta de proxy al servidor de desarrollo** de Vite
3. **Vite bloqueaba peticiones** desde el hostname `front.executor.local`
4. **Caché del navegador** almacenaba versiones antiguas

---

## Cambios Realizados

### 1. Configuración de Nginx Frontend

#### **Antes** (Configuración Incorrecta)

**Archivo**: `docker/nginx-frontend/nginx.conf`

```nginx
server {
    listen 80;
    server_name front.executor.local;
    root /home/dev/code/frontend/dist;  # ❌ Servía archivos compilados

    location / {
        try_files $uri $uri/ /index.html;
    }

    error_log stderr;
    access_log /dev/stdout;
}
```

**Problemas:**
- Servía archivos del directorio `dist` (build estático)
- No se actualizaba automáticamente con los cambios de código
- Requería compilar la app (`npm run build`) en cada cambio
- No aprovechaba Hot Module Replacement (HMR) de Vite

#### **Después** (Configuración Correcta)

**Archivo**: [nginx.conf](../docker/nginx-frontend/nginx.conf)

```nginx
server {
    listen 80;
    server_name front.executor.local;

    location / {
        # ✅ Proxy al servidor de desarrollo Vite
        proxy_pass http://excusator_node:5173;
        proxy_http_version 1.1;
        
        # Headers necesarios para WebSocket (HMR)
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Headers estándar de proxy
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        
        # No cachear para desarrollo
        proxy_cache_bypass $http_upgrade;
    }

    error_log stderr;
    access_log /dev/stdout;
}
```

**Beneficios:**
- ✅ Proxy directo al servidor Vite en `excusator_node:5173`
- ✅ Soporte para Hot Module Replacement (WebSocket)
- ✅ Cambios de código visibles inmediatamente
- ✅ No requiere builds manuales durante desarrollo

**Nota Importante:** El puerto `5173` es el puerto por defecto de Vite. Si Vite detecta que está ocupado, usa el siguiente disponible (5174, 5175, etc.). La configuración usa el nombre del servicio Docker (`excusator_node`) en lugar de IPs hardcodeadas para evitar problemas de conectividad.

---

### 2. Configuración de Vite

#### **Problema**

Vite rechazaba conexiones desde `front.executor.local` con el error:

```
To allow this host, add "front.executor.local" to `server.allowedHosts` in vite.config.js.
```

#### **Solución**

**Archivo**: [vite.config.ts](../frontend/vite.config.ts)

```typescript
import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueJsx from '@vitejs/plugin-vue-jsx'
import vueDevTools from 'vite-plugin-vue-devtools'

export default defineConfig({
  plugins: [
    vue(),
    vueJsx(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    // ✅ Whitelist del hostname usado por Traefik
    allowedHosts: ['front.executor.local'],
  },
})
```

**Explicación:**
- Por seguridad, Vite solo acepta conexiones desde `localhost` por defecto
- Al acceder vía proxy de Nginx con hostname `front.executor.local`, Vite lo rechazaba
- `allowedHosts` permite explícitamente este hostname

---

### 3. Reconstrucción del Contenedor Nginx

#### **Problema**

Los cambios en `nginx.conf` no se aplicaban automáticamente porque:
- El Dockerfile **copia** el archivo durante el `docker build`
- Los cambios en el host no se reflejan en el contenedor en ejecución

#### **Solución**

Después de modificar `docker/nginx-frontend/nginx.conf`:

```bash
# 1. Detener el contenedor
docker compose stop excusator_nginx-frontend

# 2. Reconstruir la imagen SIN caché
docker compose build --no-cache excusator_nginx-frontend

# 3. Reiniciar el contenedor
docker compose up -d excusator_nginx-frontend
```

**Dockerfile de Nginx**: [Dockerfile](../docker/nginx-frontend/Dockerfile)

```dockerfile
FROM nginx:mainline

# Copiar configuración principal de Nginx
COPY nginx-main.conf /etc/nginx/nginx.conf

# Copiar configuración del servidor (SE COPIA EN BUILD TIME)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Ajuste de permisos (UID 1000)
RUN usermod -u 1000 nginx && groupmod -g 1000 nginx
RUN mkdir -p /home/dev/code /home/dev/nginx
RUN chown -R nginx:nginx /home/dev/ /var/cache/nginx

USER 1000
WORKDIR /home/dev/code
```

**Importante:** Los archivos se copian durante `docker build`, por lo que cualquier cambio requiere rebuild.

---

### 4. Gestión del Servidor de Desarrollo Vite

#### **Problema**

El contenedor `excusator_node` usa `CMD ["tail", "-f", "/dev/null"]` por defecto, lo que mantiene el contenedor vivo pero no ejecuta el servidor Vite.

#### **Solución**

Ejecutar manualmente el servidor Vite en segundo plano:

```bash
docker compose exec excusator_node sh -c "cd /home/dev/code/frontend && npm run dev -- --host 0.0.0.0"
```

**Parámetros importantes:**
- `--host 0.0.0.0`: Permite conexiones desde cualquier IP (necesario para proxy)
- Por defecto Vite usa `--host localhost` que solo acepta conexiones locales

**Output esperado:**
```
VITE v7.3.1  ready in 525 ms

➜  Local:   http://localhost:5173/
➜  Network: http://172.18.0.4:5173/
```

**Notas:**
- El servidor debe estar corriendo para que Nginx pueda hacer proxy
- Si el contenedor se reinicia, hay que ejecutar el comando nuevamente
- Alternativa: Modificar el Dockerfile para que inicie Vite automáticamente

---

### 5. Eliminación de Archivos de Build Antiguos

#### **Problema**

El directorio `frontend/dist/` contenía un build antiguo que causaba confusión.

#### **Solución**

```bash
rm -rf /home/pabloontivero/DEV/PERSONAL/excusator-3000/frontend/dist
```

**Beneficios:**
- Evita servir archivos obsoletos accidentalmente
- Reduce confusión durante desarrollo
- El directorio se regenera automáticamente con `npm run build` si es necesario

---

### 6. Actualización del HTML Base

**Archivo**: [index.html](../frontend/index.html)

```html
<!DOCTYPE html>
<html lang="">
  <head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excusator 3000</title>  <!-- ✅ Título actualizado -->
  </head>
  <body>
    <div id="app"></div>
    <script type="module" src="/src/main.ts"></script>
  </body>
</html>
```

**Cambio:** Título actualizado de "Vite App" a "Excusator 3000" para identificación clara.

---

## Arquitectura Final

```
┌─────────────────────────────────────────────────────┐
│                    Traefik                          │
│              (Reverse Proxy)                        │
│   https://front.executor.local                      │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│          excusator_nginx-frontend                   │
│              (Nginx Proxy)                          │
│         Port 80 → proxy_pass                        │
└────────────────┬────────────────────────────────────┘
                 │
                 ▼
┌─────────────────────────────────────────────────────┐
│          excusator_node                             │
│        (Vite Dev Server)                            │
│   Port 5173 → Vue 3 Application                     │
│   - Hot Module Replacement                          │
│   - TypeScript transpilation                        │
│   - Component rendering                             │
└─────────────────────────────────────────────────────┘
```

---

## Flujo de Desarrollo

### 1. Inicio de Contenedores

```bash
cd /home/pabloontivero/DEV/PERSONAL/excusator-3000
docker compose up -d
```

### 2. Iniciar Vite Dev Server

```bash
docker compose exec excusator_node sh -c "cd /home/dev/code/frontend && npm run dev -- --host 0.0.0.0"
```

### 3. Acceder a la Aplicación

Navegar a: **https://front.executor.local**

### 4. Desarrollo

- Editar archivos en `frontend/src/`
- Vite detecta cambios automáticamente
- El navegador se recarga con HMR (sin refresh completo)

### 5. Reiniciar Nginx (solo si cambias nginx.conf)

```bash
docker compose stop excusator_nginx-frontend
docker compose build --no-cache excusator_nginx-frontend
docker compose up -d excusator_nginx-frontend
```

---

## Verificación de Funcionamiento

### Verificar Nginx está haciendo proxy

```bash
curl -k -s https://front.executor.local/ | grep -i "excusator\|vite"
```

**Output esperado:**
```html
<title>Excusator 3000</title>
```

### Verificar Vite está respondiendo

```bash
docker compose exec excusator_node sh -c "curl -s http://localhost:5173/ | head -20"
```

**Output esperado:**
```html
<!DOCTYPE html>
<html lang="">
  <head>
    <script type="module" src="/@vite/client"></script>
    ...
```

### Verificar logs de Nginx

```bash
docker compose logs --tail=20 excusator_nginx-frontend
```

**Output esperado (sin errores 502/500):**
```
excusator_nginx-frontend  | 172.18.0.2 - - [15/Jan/2026:20:44:23 +0000] "GET / HTTP/1.1" 200 301
```

---

## Troubleshooting

### Problema: "502 Bad Gateway"

**Causa:** Nginx no puede conectarse al servidor Vite

**Solución:**
1. Verificar que Vite está corriendo: `docker compose exec excusator_node ps aux | grep vite`
2. Verificar el puerto correcto en `nginx.conf` (debe coincidir con el puerto de Vite)
3. Verificar conectividad: `docker compose exec excusator_nginx-frontend ping excusator_node`

### Problema: "Host not allowed"

**Causa:** Vite rechaza el hostname

**Solución:**
1. Verificar `vite.config.ts` tiene `allowedHosts: ['front.executor.local']`
2. Reiniciar el servidor Vite después de cambiar la configuración

### Problema: Cambios no se reflejan

**Causa:** Caché del navegador o servidor Vite no detecta cambios

**Solución:**
1. Limpiar caché del navegador (Ctrl+Shift+R o Cmd+Shift+R)
2. Verificar que Vite muestra "page reload" en la consola
3. Reiniciar el servidor Vite si es necesario

### Problema: Nginx muestra configuración antigua

**Causa:** El contenedor no fue reconstruido después de cambiar `nginx.conf`

**Solución:**
```bash
docker compose stop excusator_nginx-frontend
docker compose build --no-cache excusator_nginx-frontend
docker compose up -d excusator_nginx-frontend
```

---

## Mejoras Futuras (Opcional)

### 1. Auto-start de Vite

Modificar `docker/node/Dockerfile`:

```dockerfile
# ... resto del Dockerfile ...

# Auto-iniciar Vite en desarrollo
CMD ["sh", "-c", "cd /home/dev/code/frontend && npm run dev -- --host 0.0.0.0"]
```

### 2. Mount de configuración como volumen

Modificar `docker-compose.yml` para evitar rebuilds:

```yaml
excusator_nginx-frontend:
  build: ./docker/nginx-frontend
  volumes:
    - .:/home/dev/code
    - ./docker/nginx-frontend/nginx.conf:/etc/nginx/conf.d/default.conf:ro
```

**Beneficio:** Cambios en `nginx.conf` se aplican con solo reiniciar el contenedor.

### 3. Uso de Vite Preview para staging

Para entornos de staging/producción:

```bash
# Build de producción
docker compose exec excusator_node sh -c "cd /home/dev/code/frontend && npm run build"

# Nginx vuelve a servir estáticos
# Revertir nginx.conf a: root /home/dev/code/frontend/dist;
```

---

## Resumen de Archivos Modificados

| Archivo | Cambio Principal | Propósito |
|---------|------------------|-----------|
| [nginx.conf](../docker/nginx-frontend/nginx.conf) | `root` → `proxy_pass` | Proxy a Vite dev server |
| [vite.config.ts](../frontend/vite.config.ts) | Añadido `allowedHosts` | Permitir hostname personalizado |
| [index.html](../frontend/index.html) | Título actualizado | Identificación clara de la app |

---

## Comandos de Referencia Rápida

```bash
# Iniciar entorno
docker compose up -d

# Iniciar Vite dev server
docker compose exec excusator_node sh -c "cd /home/dev/code/frontend && npm run dev -- --host 0.0.0.0"

# Rebuild Nginx tras cambios de configuración
docker compose stop excusator_nginx-frontend && \
docker compose build --no-cache excusator_nginx-frontend && \
docker compose up -d excusator_nginx-frontend

# Ver logs de Nginx
docker compose logs -f excusator_nginx-frontend

# Verificar Vite está corriendo
docker compose exec excusator_node ps aux | grep vite

# Acceso directo a Vite (bypass de Nginx)
docker compose exec excusator_node curl -s http://localhost:5173/ | head -30
```

---

**Última actualización:** 2026-01-16  
**Versión:** 1.0
