# Создание пользователя `webdev` в PostgreSQL с максимальными правами

Инструкция подготовлена для проекта `fit`, в котором PostgreSQL запущен в Docker через сервис `db`.

Текущая конфигурация проекта:

- каталог проекта: `/var/www/fit`;
- контейнер PostgreSQL: сервис `db`;
- текущая БД приложения: `laravel`;
- текущий суперпользователь PostgreSQL: `laravel`.

## Что значит "максимальные права"

В PostgreSQL максимальные права для пользователя обычно означают роль `SUPERUSER`.
Такой пользователь может:

- читать и изменять любые данные;
- создавать и удалять базы данных;
- создавать и изменять роли;
- менять владельцев объектов;
- обходить большинство ограничений доступа.

Это удобно для администрирования и разработки, но небезопасно для обычного production-пользователя приложения.

---

## 1. Перейти в каталог проекта

```bash
cd /var/www/fit
```

---

## 2. Убедиться, что контейнер PostgreSQL запущен

Если инфраструктура ещё не поднята, запустить контейнер БД:

```bash
docker compose up -d db
```

Проверить статус:

```bash
docker compose ps db
```

---

## 3. Создать пользователя `webdev` с максимальными правами

Подключиться к PostgreSQL внутри контейнера:

```bash
docker compose exec db psql -U laravel -d postgres
```

В открывшейся консоли `psql` выполнить:

```sql
CREATE ROLE webdev
WITH
    LOGIN
    SUPERUSER
    CREATEDB
    CREATEROLE
    REPLICATION
    BYPASSRLS
    PASSWORD 'CHANGE_ME_STRONG_PASSWORD';
```

Если пользователь `webdev` уже существует, вместо создания можно обновить ему права и пароль:

```sql
ALTER ROLE webdev
WITH
    LOGIN
    SUPERUSER
    CREATEDB
    CREATEROLE
    REPLICATION
    BYPASSRLS
    PASSWORD 'CHANGE_ME_STRONG_PASSWORD';
```

Выйти из `psql`:

```sql
\q
```

---

## 4. Назначить пользователя владельцем БД `laravel` (рекомендуется)

Снова подключиться к PostgreSQL:

```bash
docker compose exec db psql -U laravel -d postgres
```

Выполнить:

```sql
ALTER DATABASE laravel OWNER TO webdev;
```

Затем подключиться уже к базе `laravel`:

```sql
\c laravel
```

И назначить владельца схемы `public`:

```sql
ALTER SCHEMA public OWNER TO webdev;
```

При необходимости можно дополнительно выдать явные права на существующие объекты:

```sql
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO webdev;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO webdev;
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO webdev;
```

И настроить права по умолчанию для новых объектов:

```sql
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON TABLES TO webdev;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON SEQUENCES TO webdev;
ALTER DEFAULT PRIVILEGES IN SCHEMA public GRANT ALL ON FUNCTIONS TO webdev;
```

Выйти:

```sql
\q
```

Примечание: для `SUPERUSER` это обычно не обязательно, но удобно, чтобы база и схема явно принадлежали `webdev`.

---

## 5. Как ограничить права `webdev` для полноценной работы приложения

Если пользователь `webdev` нужен именно для Laravel, обычно достаточно убрать у него глобальные административные права, но оставить его владельцем рабочей БД `laravel` и схемы `public`.

Такой вариант подходит для:

- запуска приложения;
- выполнения миграций;
- работы сидеров;
- создания служебных таблиц Laravel, если они используются приложением.

Подключиться к PostgreSQL:

```bash
docker compose exec db psql -U laravel -d postgres
```

Убрать опасные глобальные привилегии, оставив только возможность входа:

```sql
ALTER ROLE webdev
WITH
    LOGIN
    NOSUPERUSER
    NOCREATEDB
    NOCREATEROLE
    NOREPLICATION
    NOBYPASSRLS
    PASSWORD 'CHANGE_ME_STRONG_PASSWORD';
```

Убедиться, что база приложения принадлежит `webdev`:

```sql
ALTER DATABASE laravel OWNER TO webdev;
GRANT CONNECT, TEMPORARY ON DATABASE laravel TO webdev;
```

Переключиться на рабочую БД:

```sql
\c laravel
```

Назначить владельца схемы `public` и дать права на создание объектов внутри неё:

```sql
ALTER SCHEMA public OWNER TO webdev;
GRANT USAGE, CREATE ON SCHEMA public TO webdev;
```

Если существующие таблицы, последовательности и функции были созданы другим пользователем, передать их `webdev`:

```sql
REASSIGN OWNED BY laravel TO webdev;
```

Если в базе есть объекты от разных владельцев и переносить владение не хочется, можно выдать явные права:

```sql
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO webdev;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO webdev;
GRANT ALL PRIVILEGES ON ALL FUNCTIONS IN SCHEMA public TO webdev;
```

Выйти:

```sql
\q
```

Итог: пользователь `webdev` больше не является суперпользователем, но остаётся достаточно привилегированным для полной работы приложения в БД `laravel`.

---

## 6. Обновить настройки приложения

Если Laravel должен подключаться под новым пользователем, открыть файл:

```bash
nano /var/www/fit/.env
```

Заменить параметры БД на:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=webdev
DB_PASSWORD=CHANGE_ME_STRONG_PASSWORD
```

После изменения `.env` очистить и пересобрать кэши Laravel:

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan optimize
```

---

## 7. Проверить, что пользователь создан

Проверка списка ролей:

```bash
docker compose exec db psql -U laravel -d postgres -c "\du"
```

Проверка владельца базы:

```bash
docker compose exec db psql -U laravel -d postgres -c "\l+"
```

Проверка подключения под новым пользователем:

```bash
docker compose exec db psql -U webdev -d laravel -c "SELECT current_user, current_database();"
```

Ожидаемо в результате должен быть пользователь `webdev` и база `laravel`.

---

## 8. Важные замечания

1. Для production-приложения обычно не рекомендуется использовать `SUPERUSER` в качестве пользователя Laravel.
2. Безопаснее разделять роли:
   - отдельный суперпользователь для администрирования;
   - отдельный ограниченный пользователь для приложения.
3. Обязательно замените `CHANGE_ME_STRONG_PASSWORD` на сложный уникальный пароль.
4. Для production обычно оптимален вариант без `SUPERUSER`, описанный в разделе выше.
5. Если PostgreSQL уже содержит важные данные, перед изменением владельца БД желательно сделать резервную копию.

---

## 9. Краткий вариант команд

Если нужен только быстрый минимальный набор команд для ограниченного, но полнофункционального пользователя приложения:

```bash
cd /var/www/fit
docker compose up -d db
docker compose exec db psql -U laravel -d postgres -c "CREATE ROLE webdev WITH LOGIN PASSWORD 'CHANGE_ME_STRONG_PASSWORD';"
docker compose exec db psql -U laravel -d postgres -c "ALTER ROLE webdev WITH LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOREPLICATION NOBYPASSRLS PASSWORD 'CHANGE_ME_STRONG_PASSWORD';"
docker compose exec db psql -U laravel -d postgres -c "ALTER DATABASE laravel OWNER TO webdev;"
docker compose exec db psql -U laravel -d laravel -c "ALTER SCHEMA public OWNER TO webdev;"
docker compose exec db psql -U laravel -d laravel -c "GRANT USAGE, CREATE ON SCHEMA public TO webdev;"
docker compose exec db psql -U laravel -d postgres -c "\du"
```

После этого при необходимости обновите `DB_USERNAME` и `DB_PASSWORD` в `.env`.
