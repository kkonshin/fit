# Firewall и базовая безопасность Ubuntu 22.04 для `fit`

Инструкция подготовлена для сервера `193.160.209.121`, домена `4303723-ea97251.twc1.net` и проекта `/var/www/fit`.

Проект запускается в Docker-контейнерах. Публично должны быть доступны только:

- `22/tcp` для SSH;
- `80/tcp` для HTTP, если нужен редирект на HTTPS;
- `443/tcp` для HTTPS.

Внутренний порт приложения `8080` должен слушать только `127.0.0.1`, как описано в `DEPLOY.md`. PostgreSQL, Redis, Memcached и внутренний nginx Docker не должны быть доступны из интернета.

## Оглавление

1. [Перед началом](#before-start)
2. [Обновление системы](#system-update)
3. [Настройка SSH](#ssh)
4. [Настройка `ufw`](#ufw)
5. [Проверка Docker-портов](#docker-ports)
6. [Установка и настройка `fail2ban`](#fail2ban)
7. [Автоматические security-обновления](#unattended-upgrades)
8. [Базовое усиление `nginx`](#nginx-hardening)
9. [Права на файлы проекта и секреты](#file-permissions)
10. [Проверка открытых портов](#port-check)
11. [Логи и регулярный аудит](#audit)
12. [Чек-лист после настройки](#checklist)

<a id="before-start"></a>
## 1. Перед началом

Подключиться к серверу по SSH:

```bash
ssh webdev@193.160.209.121
```

Убедиться, что есть доступ к `sudo`:

```bash
sudo -v
```

Перед включением firewall важно не потерять SSH-доступ. Если SSH работает на нестандартном порту, замените `22/tcp` во всех командах ниже на фактический порт.

Узнать порт SSH можно так:

```bash
sudo ss -tulpn | grep sshd
```

<a id="system-update"></a>
## 2. Обновление системы

Обновить список пакетов и установить security-обновления:

```bash
sudo apt update
sudo apt upgrade -y
```

Установить базовые инструменты:

```bash
sudo apt install -y ufw fail2ban unattended-upgrades apt-listchanges curl ca-certificates
```

Если после обновления система попросит перезагрузку, выполнить её в удобное время:

```bash
sudo reboot
```

<a id="ssh"></a>
## 3. Настройка SSH

### 3.1. Добавить SSH-ключ

На локальной машине, если ключа ещё нет:

```bash
ssh-keygen -t ed25519 -C "fit-server"
```

Передать публичный ключ на сервер:

```bash
ssh-copy-id webdev@193.160.209.121
```

Проверить вход по ключу в новой SSH-сессии:

```bash
ssh webdev@193.160.209.121
```

Не закрывайте старую SSH-сессию, пока новая не проверена.

### 3.2. Отключить root-login и вход по паролю

Открыть конфигурацию SSH:

```bash
sudo nano /etc/ssh/sshd_config
```

Рекомендуемые параметры:

```sshconfig
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
KbdInteractiveAuthentication no
X11Forwarding no
MaxAuthTries 3
ClientAliveInterval 300
ClientAliveCountMax 2
```

Проверить конфигурацию:

```bash
sudo sshd -t
```

Применить изменения:

```bash
sudo systemctl reload ssh
```

После этого снова открыть новую SSH-сессию и убедиться, что вход по ключу работает.

<a id="ufw"></a>
## 4. Настройка `ufw`

Сбросить старые правила, если сервер настраивается с нуля:

```bash
sudo ufw --force reset
```

Политика по умолчанию:

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

Разрешить SSH:

```bash
sudo ufw allow 22/tcp comment 'SSH'
```

Разрешить HTTP и HTTPS для внешнего `nginx`:

```bash
sudo ufw allow 80/tcp comment 'HTTP'
sudo ufw allow 443/tcp comment 'HTTPS'
```

Включить firewall:

```bash
sudo ufw enable
```

Проверить статус:

```bash
sudo ufw status verbose
```

Ожидаемо должны быть разрешены только `22/tcp`, `80/tcp` и `443/tcp`.

### 4.1. Если SSH нужен только с одного IP

Если есть постоянный IP администратора, безопаснее ограничить SSH:

```bash
sudo ufw delete allow 22/tcp
sudo ufw allow from YOUR_STATIC_IP to any port 22 proto tcp comment 'SSH from admin IP'
```

Замените `YOUR_STATIC_IP` на реальный внешний IP. Не используйте этот вариант, если IP часто меняется.

### 4.2. Что не нужно открывать

Не открывайте наружу:

- `5432/tcp` PostgreSQL;
- `6379/tcp` Redis;
- `11211/tcp` Memcached;
- `8080/tcp` внутренний порт приложения;
- любые Docker-порты, которые не предназначены для публичного доступа.

<a id="docker-ports"></a>
## 5. Проверка Docker-портов

Docker может публиковать порты через собственные правила `iptables`, поэтому одной настройки `ufw` недостаточно. Важно убедиться, что контейнеры не проброшены на внешний интерфейс.

В каталоге проекта:

```bash
cd /var/www/fit
docker compose ps
```

Проверить слушающие порты:

```bash
sudo ss -tulpn
```

Для проекта `fit` нормальная схема:

```text
0.0.0.0:22      sshd
0.0.0.0:80      nginx
0.0.0.0:443     nginx
127.0.0.1:8080  docker-proxy или nginx внутри Docker
```

Если `8080`, `5432`, `6379` или `11211` слушают на `0.0.0.0`, это нужно исправить в `docker-compose.yml`: публиковать такие порты только на `127.0.0.1` или не публиковать вовсе.

Пример безопасной публикации внутреннего HTTP-порта:

```yaml
ports:
  - "127.0.0.1:8080:80"
```

После изменения Docker-конфигурации пересоздать нужные контейнеры:

```bash
docker compose up -d --force-recreate
```

<a id="fail2ban"></a>
## 6. Установка и настройка `fail2ban`

`fail2ban` блокирует IP-адреса после серии неудачных попыток входа.

Создать локальную конфигурацию:

```bash
sudo nano /etc/fail2ban/jail.local
```

Минимальная конфигурация:

```ini
[DEFAULT]
bantime = 1h
findtime = 10m
maxretry = 5
backend = systemd

[sshd]
enabled = true
port = 22
filter = sshd
logpath = %(sshd_log)s
```

Если SSH работает на другом порту, заменить `port = 22`.

Включить сервис:

```bash
sudo systemctl enable --now fail2ban
```

Проверить статус:

```bash
sudo fail2ban-client status
sudo fail2ban-client status sshd
```

Разблокировать IP при ошибочной блокировке:

```bash
sudo fail2ban-client set sshd unbanip YOUR_IP
```

<a id="unattended-upgrades"></a>
## 7. Автоматические security-обновления

Включить автоматическую установку обновлений безопасности:

```bash
sudo dpkg-reconfigure --priority=low unattended-upgrades
```

Проверить файл:

```bash
sudo nano /etc/apt/apt.conf.d/50unattended-upgrades
```

Убедиться, что включены security-обновления:

```text
"${distro_id}:${distro_codename}-security";
```

Проверить расписание:

```bash
sudo nano /etc/apt/apt.conf.d/20auto-upgrades
```

Рекомендуемые значения:

```text
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Unattended-Upgrade "1";
APT::Periodic::AutocleanInterval "7";
```

Проверить работу в тестовом режиме:

```bash
sudo unattended-upgrades --dry-run --debug
```

<a id="nginx-hardening"></a>
## 8. Базовое усиление `nginx`

Открыть основную конфигурацию:

```bash
sudo nano /etc/nginx/nginx.conf
```

Внутри блока `http { ... }` скрыть версию nginx:

```nginx
server_tokens off;
```

В конфигурации сайта `/etc/nginx/sites-available/fit.conf` можно добавить security-заголовки в HTTPS server-блок:

```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "geolocation=(), microphone=(), camera=()" always;
```

Если используется обычный доверенный HTTPS-сертификат, например Let's Encrypt, можно добавить HSTS:

```nginx
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
```

С самоподписанным сертификатом HSTS лучше не включать, чтобы не закрепить в браузерах проблемную HTTPS-конфигурацию.

Проверить и применить:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

<a id="file-permissions"></a>
## 9. Права на файлы проекта и секреты

Каталог проекта:

```bash
cd /var/www/fit
```

Файл `.env` должен быть доступен только владельцу и группе:

```bash
sudo chmod 640 .env
```

Если проектом управляет пользователь `webdev`, выставить владельца:

```bash
sudo chown -R webdev:webdev /var/www/fit
```

Для Laravel директории `storage` и `bootstrap/cache` должны быть доступны процессу приложения. Если контейнер использует UID/GID пользователя `webdev`, обычно достаточно:

```bash
chmod -R ug+rw storage bootstrap/cache
```

Не храните в репозитории:

- `.env`;
- приватные SSH-ключи;
- приватные SSL-ключи;
- дампы базы данных;
- резервные копии с реальными персональными данными.

Проверить, что `.env` не отслеживается git:

```bash
git status --short .env
git check-ignore .env
```

<a id="port-check"></a>
## 10. Проверка открытых портов

На сервере:

```bash
sudo ss -tulpn
sudo ufw status numbered
```

С локальной машины можно проверить внешнюю доступность:

```bash
nmap -Pn -p 22,80,443,8080,5432,6379,11211 193.160.209.121
```

Ожидаемо:

- `22/tcp` открыт, если SSH разрешён;
- `80/tcp` открыт, если используется редирект HTTP на HTTPS;
- `443/tcp` открыт;
- `8080/tcp`, `5432/tcp`, `6379/tcp`, `11211/tcp` закрыты снаружи.

Если `nmap` не установлен:

```bash
sudo apt install -y nmap
```

<a id="audit"></a>
## 11. Логи и регулярный аудит

Проверить попытки SSH-входа:

```bash
sudo journalctl -u ssh --since "24 hours ago"
```

Проверить блокировки `fail2ban`:

```bash
sudo fail2ban-client status sshd
sudo journalctl -u fail2ban --since "24 hours ago"
```

Проверить логи `nginx`:

```bash
sudo journalctl -u nginx --since "24 hours ago"
sudo tail -f /var/log/nginx/access.log /var/log/nginx/error.log
```

Проверить Docker-контейнеры:

```bash
cd /var/www/fit
docker compose ps
docker compose logs --tail=100 app webserver
```

Периодически обновлять Docker-образы и зависимости проекта по отдельному плану, с резервной копией БД и проверкой приложения после обновления.

<a id="checklist"></a>
## 12. Чек-лист после настройки

1. Вход по SSH-ключу работает в новой сессии.
2. Вход по паролю и root-login отключены.
3. `ufw` включён и разрешает только нужные входящие порты.
4. Снаружи доступны только `22`, `80` и `443`.
5. Docker-порт `8080` слушает только `127.0.0.1`.
6. PostgreSQL, Redis и Memcached не доступны из интернета.
7. `fail2ban` запущен и защищает `sshd`.
8. Security-обновления Ubuntu устанавливаются автоматически.
9. `.env` и приватные ключи не попадают в git.
10. После изменений `nginx -t` проходит без ошибок, сайт открывается по HTTPS.

## Итог

После выполнения инструкции сервер принимает публичный трафик только на SSH, HTTP и HTTPS. Приложение остаётся за внешним `nginx`, внутренние Docker-сервисы не открыты в интернет, SSH защищён ключами и `fail2ban`, а критические обновления Ubuntu устанавливаются автоматически.
