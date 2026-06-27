# Деплой `fit` по HTTPS через `nginx` с самоподписанным сертификатом

Инструкция для сервера `193.160.209.121` и домена `4303723-ea97251.twc1.net`.

Проект расположен в каталоге `/var/www/fit` и запускается в Docker-контейнерах.
HTTPS будет завершаться на системном `nginx` хоста, а затем трафик будет проксироваться во внутренний `nginx` контейнера Docker.

## Оглавление

1. [Схема работы](#scheme)
2. [Установка пакетов на сервере](#install-packages)
3. [Проверка домена](#check-domain)
4. [Подготовка проекта](#prepare-project)
5. [Рекомендуемая настройка внутреннего `nginx` в Docker](#docker-nginx)
6. [Сборка и запуск контейнеров](#build-and-run)
7. [Генерация и перенос дампа базы данных](#db-dump)
8. [Генерация самоподписанного сертификата](#ssl-cert)
9. [Настройка внешнего `nginx` на сервере](#host-nginx)
10. [Проверка работы](#verification)
11. [Полезная диагностика](#diagnostics)
12. [Что важно учесть](#important-notes)
13. [Итог](#summary)

<a id="scheme"></a>
## Схема работы

1. Системный `nginx` на сервере:
   - принимает запросы на `80` и `443`;
   - перенаправляет HTTP на HTTPS;
   - использует самоподписанный сертификат;
   - проксирует запросы на `127.0.0.1:8080`.

2. Контейнерный `nginx` из проекта:
   - обслуживает Laravel-приложение по HTTP на порту `80`;
   - уже проброшен наружу только локально: `127.0.0.1:8080`.

Это удобно, потому что:
- SSL настраивается только на хосте;
- контейнеры не нужно усложнять сертификатами;
- внутренний HTTP-порт приложения не торчит наружу.

<a id="install-packages"></a>
## 1. Установка пакетов на сервере

```bash
sudo apt update
sudo apt install -y nginx openssl docker.io docker-compose-plugin
```

Если включён `ufw`, открыть HTTP и HTTPS:

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

Включить сервисы:

```bash
sudo systemctl enable --now docker
sudo systemctl enable --now nginx
```

<a id="check-domain"></a>
## 2. Проверка домена

Убедиться, что домен `4303723-ea97251.twc1.net` указывает на сервер `193.160.209.121`.

Проверка:

```bash
dig +short 4303723-ea97251.twc1.net
```

Ожидаемый результат:

```text
193.160.209.121
```

<a id="prepare-project"></a>
## 3. Подготовка проекта

Перейти в каталог проекта:

```bash
cd /var/www/fit
```

Если `.env` ещё не создан:

```bash
cp .env.example .env
```

Открыть `.env` и проверить основные параметры:

```env
APP_NAME=fit
APP_ENV=production
APP_DEBUG=false
APP_URL=https://4303723-ea97251.twc1.net

APP_PORT=8080
COMPOSE_PROJECT_NAME=fit
PUID=1000
PGID=1000
```

`PUID` и `PGID` должны совпадать с UID/GID пользователя, от которого запускается `docker compose` на сервере. Узнать их можно так:

```bash
id webdev
```

или, если команды запускаются от текущего пользователя:

```bash
id
```

Если приложение будет использовать PostgreSQL из `docker-compose.yml`, проверьте также настройки БД в `.env`.

Для frontend-сборки проект требует `Node.js 22.13+`. В `docker-compose.yml` сервис `node` использует тот же образ, что и `app`, поэтому внутри него доступны и `Node.js`, и `php artisan`, который нужен Vite-плагинам во время сборки.

<a id="docker-nginx"></a>
## 4. Рекомендуемая настройка внутреннего nginx в Docker

Открыть файл:

```bash
nano /var/www/fit/docker/nginx/conf.d/nginx.conf
```

Заменить:

```nginx
server_name localhost 192.168.1.10;
```

на:

```nginx
server_name 4303723-ea97251.twc1.net 193.160.209.121 localhost;
```

Это не строго обязательно для reverse proxy, но лучше явно указать домен.

<a id="build-and-run"></a>
## 5. Сборка и запуск контейнеров

Собрать PHP-образ:

```bash
docker compose build app
```

Если вы меняли `PUID`/`PGID`, образ нужно обязательно пересобрать, иначе `www-data` внутри контейнера останется с прежним UID/GID.

Поднять инфраструктуру:

```bash
docker compose up -d db redis memcached
```

<a id="db-dump"></a>
### 5.1. Генерация и перенос дампа базы данных

Если нужно перенести существующие данные в production, сделайте это до запуска миграций.

Создать дамп PostgreSQL на исходной машине, где сейчас находится актуальная БД:

```bash
docker compose exec -T db pg_dump -U laravel -d laravel -Fc --no-owner --no-privileges > fit-$(date +%F).dump
```

Если исходная БД запущена не в Docker, можно использовать обычный `pg_dump` с теми же параметрами:

```bash
pg_dump -h 127.0.0.1 -p 5432 -U laravel -d laravel -Fc --no-owner --no-privileges > fit-$(date +%F).dump
```

Передать дамп на production-сервер:

```bash
scp fit-$(date +%F).dump webdev@193.160.209.121:~/fit-db.dump
```

Подключиться к production-серверу и восстановить дамп в контейнер `db`:

```bash
ssh webdev@193.160.209.121
cd /var/www/fit
docker compose exec -T db pg_restore -U laravel -d laravel --clean --if-exists --no-owner --no-privileges < ~/fit-db.dump
```

Если дамп заливается в уже работающее production-приложение, на время восстановления лучше включить maintenance mode:

```bash
docker compose exec app php artisan down
docker compose exec -T db pg_restore -U laravel -d laravel --clean --if-exists --no-owner --no-privileges < ~/fit-db.dump
docker compose exec app php artisan up
```

После восстановления дампа можно продолжать деплой: выполнить миграции, чтобы подтянуть возможные новые изменения схемы, и затем прогреть кэши Laravel.

Установить PHP-зависимости:

```bash
docker compose run --rm app composer install --no-dev --optimize-autoloader
```

Установить Node-зависимости:

```bash
docker compose run --rm node ci
```

Если `npm` падает с `EACCES` на `node_modules`, значит каталог был создан другим пользователем. Исправить можно так:

```bash
sudo chown -R webdev:webdev /var/www/fit/node_modules /var/www/fit/.npm
```

или удалить старые каталоги и установить зависимости заново:

```bash
sudo rm -rf /var/www/fit/node_modules /var/www/fit/.npm
docker compose run --rm node ci
```

Собрать frontend для production:

```bash
docker compose run --rm node run build
```

Сгенерировать ключ приложения:

```bash
docker compose run --rm app php artisan key:generate --force
```

Применить миграции:

```bash
docker compose run --rm app php artisan migrate --force
```

При необходимости создать симлинк для storage:

```bash
docker compose run --rm app php artisan storage:link
```

Поднять приложение и внутренний `nginx`:

```bash
docker compose up -d app webserver
```

Прогреть/обновить кэши Laravel:

```bash
docker compose exec app php artisan optimize
```

Проверить статус контейнеров:

```bash
docker compose ps
```

<a id="ssl-cert"></a>
## 6. Генерация самоподписанного сертификата

Создать каталог под сертификаты:

```bash
sudo mkdir -p /etc/nginx/ssl
```

Сгенерировать сертификат и ключ:

```bash
sudo openssl req -x509 -nodes -days 825 -newkey rsa:4096 \
  -keyout /etc/nginx/ssl/fit-selfsigned.key \
  -out /etc/nginx/ssl/fit-selfsigned.crt \
  -subj "/C=RU/ST=RU/L=RU/O=fit/CN=4303723-ea97251.twc1.net" \
  -addext "subjectAltName=DNS:4303723-ea97251.twc1.net,IP:193.160.209.121"
```

Выдать корректные права:

```bash
sudo chmod 600 /etc/nginx/ssl/fit-selfsigned.key
sudo chmod 644 /etc/nginx/ssl/fit-selfsigned.crt
```

<a id="host-nginx"></a>
## 7. Настройка внешнего nginx на сервере

Создать файл конфигурации:

```bash
sudo nano /etc/nginx/sites-available/fit.conf
```

Вставить:

```nginx
server {
    listen 80;
    server_name 4303723-ea97251.twc1.net 193.160.209.121;

    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name 4303723-ea97251.twc1.net 193.160.209.121;

    ssl_certificate     /etc/nginx/ssl/fit-selfsigned.crt;
    ssl_certificate_key /etc/nginx/ssl/fit-selfsigned.key;

    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    client_max_body_size 100M;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;

        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port 443;

        proxy_redirect off;
    }
}
```

Активировать конфигурацию:

```bash
sudo ln -s /etc/nginx/sites-available/fit.conf /etc/nginx/sites-enabled/fit.conf
```

Если включён сайт по умолчанию, отключить:

```bash
sudo rm -f /etc/nginx/sites-enabled/default
```

Проверить конфигурацию:

```bash
sudo nginx -t
```

Если ошибок нет, перезагрузить `nginx`:

```bash
sudo systemctl reload nginx
```

<a id="verification"></a>
## 8. Проверка работы

Проверить, что внутренний контейнерный `nginx` отвечает локально:

```bash
curl -I http://127.0.0.1:8080
```

Проверить HTTPS с игнорированием недоверенного сертификата:

```bash
curl -kI https://4303723-ea97251.twc1.net
```

Открыть сайт в браузере:

```text
https://4303723-ea97251.twc1.net
```

Важно: браузер покажет предупреждение безопасности, потому что сертификат самоподписанный. Это нормально. Нужно вручную подтвердить исключение.

<a id="diagnostics"></a>
## 9. Полезная диагностика

Логи Docker-контейнеров:

```bash
docker compose logs -f app
docker compose logs -f webserver
```

Логи системного `nginx`:

```bash
sudo journalctl -u nginx -f
```

Проверка открытых портов:

```bash
sudo ss -tulpn | grep -E ':80|:443|:8080'
```

<a id="important-notes"></a>
## 10. Что важно учесть

1. Самоподписанный сертификат подходит для тестового, внутреннего или личного использования.
2. Для публичного production-сайта лучше использовать Let's Encrypt.
3. Если приложение будет генерировать ссылки с `http`, а не `https`, проверьте:
   - `APP_URL=https://4303723-ea97251.twc1.net`;
   - наличие заголовка `X-Forwarded-Proto https` в конфиге внешнего `nginx`;
   - корректную работу Laravel за reverse proxy.
4. После изменений в `docker/nginx/conf.d/nginx.conf` нужно перезапустить контейнер:

```bash
docker compose up -d --force-recreate webserver
```

<a id="summary"></a>
## 11. Итог

После выполнения шагов сайт должен открываться по адресу:

```text
https://4303723-ea97251.twc1.net
```

Внешний `nginx` на сервере будет принимать HTTPS-трафик и передавать его во внутренний `nginx` Docker-контейнера, который обслуживает приложение из `/var/www/fit`.
