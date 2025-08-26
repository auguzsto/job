FROM php:8.3-cli-alpine

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN addgroup -g ${HOST_GID} job && \
    adduser -u ${HOST_UID} -G job -s /bin/ash -D job

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /usr/src/myapp
RUN chown -R job:job .
USER job