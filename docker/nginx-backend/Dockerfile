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
