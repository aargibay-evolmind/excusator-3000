FROM node:20

# Asegurar usuario node con UID 1000
RUN if [ "$(id -u node)" != "1000" ]; then \
        usermod -u 1000 node && groupmod -g 1000 node; \
    fi

RUN mkdir -p /home/dev/code
RUN chown -R node:node /home/dev/

USER 1000
WORKDIR /home/dev/code

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
