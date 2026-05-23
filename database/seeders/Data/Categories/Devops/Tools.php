<?php

namespace Database\Seeders\Data\Categories\Devops;

class Tools
{
    public static function all(): array
    {
        return [
            [
                'category' => 'DevOps',
                'question' => 'Что такое shell простыми словами?',
                'answer' => '**Shell** — это **программа-оболочка**, которая принимает текстовые команды от пользователя и выполняет их в операционной системе. Когда ты открываешь терминал и пишешь `ls` или `git status` — ты общаешься с shell, а он уже дёргает нужные программы.

Популярные shell:

- **`bash`** (Bourne Again Shell) — стандарт по умолчанию в Linux
- **`zsh`** — улучшенный bash, по умолчанию в macOS с 2019
- **`PowerShell`** и **`cmd`** — на Windows

**Терминал и shell — разные вещи**: терминал это окно ввода-вывода, shell — программа, которая внутри него работает.',
                'code_example' => '# Узнать какой shell сейчас используется
echo $SHELL          # /bin/bash или /usr/bin/zsh

# Список доступных shell на машине
cat /etc/shells',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают команды cd, ls, pwd?',
                'answer' => 'Три базовые команды **навигации по файловой системе**.

**`cd <папка>`** (change directory) — сменить текущую папку:

- `cd ..` — на уровень вверх
- `cd ~` — в домашнюю папку
- `cd /` — в корень
- `cd -` — вернуться в предыдущую

**`ls`** (list) — показать содержимое папки:

- `ls -l` — подробно (права, владелец, размер)
- `ls -a` — со скрытыми (которые начинаются с точки)
- `ls -la` — всё вместе
- `ls -lh` — размеры в человеко-читаемом виде

**`pwd`** (print working directory) — напечатать **полный путь** к текущей папке. Удобно, когда заблудился в дереве каталогов.',
                'code_example' => '$ pwd
/home/user

$ ls -la
drwxr-xr-x  3 user user 4096 May 20 10:00 .
drwxr-xr-x  5 root root 4096 May 19 09:00 ..
-rw-r--r--  1 user user  220 May 19 09:00 .bashrc
drwxr-xr-x  2 user user 4096 May 20 10:00 projects

$ cd projects && pwd
/home/user/projects',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое grep простыми словами?',
                'answer' => '**`grep`** — утилита для **поиска строк, которые соответствуют шаблону**, внутри файлов или потока ввода. Название от **global regular expression print**.

Простой случай: `grep "TODO" file.php` — выведет все строки с TODO.

Полезные флаги:

- `-r` — **рекурсивно** по папке
- `-i` — **без учёта регистра**
- `-n` — показывать **номера строк**
- `-v` — **инвертировать** (строки, которые НЕ совпадают)
- `-E` — расширенные регулярки
- `-l` — только **имена файлов** с совпадением

Часто стоит в **pipe-цепочках**: `cat log | grep ERROR`.

Современные альтернативы — **`ripgrep` (`rg`)** и **`ag`** (silver searcher): быстрее и по умолчанию игнорируют `.gitignore`.',
                'code_example' => '# Найти TODO в одном файле с номерами строк
grep -n "TODO" app/Models/User.php

# Рекурсивно искать API_KEY в проекте, без учёта регистра
grep -rin "api_key" .

# Поиск в логе через pipe + подсчёт совпадений
tail -f storage/logs/laravel.log | grep --line-buffered ERROR
cat access.log | grep -c "500 "',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое find простыми словами?',
                'answer' => '**`find`** — команда для **рекурсивного поиска файлов** по имени, типу, размеру, дате изменения, правам.

Базовый синтаксис: `find <где> <условия> [-exec действие]`.

**Самые частые флаги:**

- `-name "*.php"` — по шаблону имени
- `-iname "*.PHP"` — то же, регистронезависимо
- `-type f` — только файлы, `-type d` — только папки
- `-mtime -1` — изменённые за последние сутки
- `-size +10M` — больше 10 МБ
- `-path "*/vendor/*" -prune` — исключить папку из обхода
- `-exec rm {} \;` — выполнить команду над каждым найденным',
                'code_example' => '# Все PHP-файлы рекурсивно
find . -name "*.php"

# Папки с именем logs
find . -type d -name "logs"

# Изменённые за первый день
find . -mtime -1

# Файлы больше 10 МБ
find /var/log -type f -size +10M

# Поиск с исключением vendor/ и node_modules/
find . -type f -name "*.php" \
    -not -path "./vendor/*" \
    -not -path "./node_modules/*"

# Удалить все .log старше 7 дней
find /var/log -name "*.log" -mtime +7 -delete',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое pipe (|) в shell простыми словами?',
                'answer' => '**Pipe** (`|`) передаёт **stdout одной команды** на **stdin другой**. Это основной механизм Unix: маленькие утилиты, склеенные в цепочку, делают сложную работу.

Аналогия: труба, по которой данные текут от одной программы к другой.

**Цепочки можно делать сколько угодно** — каждая команда получает результат предыдущей. Так строятся однострочные «pipelines» для анализа логов и поиска.',
                'code_example' => '# Список файлов → отфильтровать только .log
ls | grep ".log"

# Посчитать число ERROR-строк в логе
cat storage/logs/laravel.log | grep ERROR | wc -l

# Топ-10 IP по числу обращений в access.log
cat /var/log/nginx/access.log \
    | awk \'{print $1}\' \
    | sort \
    | uniq -c \
    | sort -rn \
    | head -10

# Только уникальные ошибки за последний час
tail -n 10000 laravel.log | grep ERROR | sort -u',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое перенаправление > и >> в shell?',
                'answer' => 'Перенаправление **записывает вывод команды в файл** вместо терминала.

**Базовые операторы:**

- `>` — **перезаписать** файл (если есть — затрётся)
- `>>` — **дописать в конец** файла
- `<` — взять stdin **из файла**

**Каналы потоков:**

- `1>` или просто `>` — stdout (обычный вывод)
- `2>` — stderr (ошибки)
- `&>` или `> file 2>&1` — **оба потока** в один файл
- `2>/dev/null` — выбросить ошибки в «никуда»

Это позволяет складывать вывод скриптов в логи и разделять успехи и ошибки.',
                'code_example' => '# Перезаписать / дописать
echo "hi" > log.txt
echo "hi2" >> log.txt

# Только ошибки в отдельный файл
php artisan migrate 2> errors.log

# И stdout, и stderr в один файл
php artisan queue:work &> queue.log
# то же самое:
php artisan queue:work > queue.log 2>&1

# Спрятать ошибки (например, отсутствие файла)
grep "TODO" *.php 2>/dev/null

# Взять stdin из файла
mysql -u root my_db < dump.sql',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое переменные окружения простыми словами?',
                'answer' => '**Переменные окружения** — глобальные пары `KEY=VALUE`, которые **наследуются дочерними процессами** от родителя. Через них приложениям передают **конфиг снаружи** — без правки кода.

**Часто встречающиеся:**

- `PATH` — список папок, где shell ищет команды
- `HOME` — путь к домашней папке (`/home/user`)
- `USER`, `SHELL`, `LANG`
- `DATABASE_URL`, `APP_ENV`, `APP_KEY` — конфиг приложения

**Где задают:**

- временно — `export DB_HOST=localhost` (только в текущей сессии)
- постоянно — в `~/.bashrc`, `~/.zshrc`, `~/.profile`
- для одного процесса — `DB_HOST=localhost php artisan ...`
- из файла — `.env` + `vlucas/phpdotenv` в Laravel
- в проде — через `systemd`, Docker `-e`, k8s `env:` или `envFrom:`

**Это главный механизм 12-factor app** — конфиг живёт в окружении, а не в коде.',
                'code_example' => '# Посмотреть
echo $HOME
echo $PATH
env | grep DB_       # все переменные с префиксом DB_
printenv USER

# Задать на сессию
export DB_HOST=localhost
export APP_ENV=production

# Для одной команды (не остаётся в окружении)
DB_HOST=localhost php artisan migrate

# Постоянно — добавить в ~/.bashrc
echo \'export EDITOR=nvim\' >> ~/.bashrc

# В PHP читать
$host = getenv("DB_HOST");
// в Laravel — env("DB_HOST") (только в config-файлах)',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое .env файл простыми словами?',
                'answer' => '**`.env`** — текстовый файл в корне проекта со списком **переменных окружения** для приложения: `DB_HOST`, `APP_KEY`, `MAIL_PASSWORD` и т.п. Каждая строка — пара `KEY=VALUE`.

**Главное правило**: `.env` **НЕ коммитится в git** (он в `.gitignore`), потому что содержит **секреты** (пароли БД, токены API, ключи).

Вместе с ним коммитится **`.env.example`** — шаблон с теми же ключами, но пустыми или demo-значениями, чтобы коллега, клонировав репозиторий, знал какие переменные ему нужно заполнить.

В **Laravel** файл подгружается автоматически на старте через `vlucas/phpdotenv`, значения доступны через:

- `env("DB_HOST")` — напрямую (только в config-файлах)
- `config("database.connections.mysql.host")` — рекомендованный путь в коде

На **stage** и **prod** значения отличаются от **dev** — в этом и смысл вынесения конфига наружу (**12-factor app**).',
                'code_example' => '# .env (НЕ коммитится)
APP_NAME=MyShop
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=shop
DB_PASSWORD=secret123

# .env.example (коммитится)
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=laravel
DB_PASSWORD=',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают cat, less, head, tail?',
                'answer' => 'Четыре базовых способа **посмотреть содержимое текстового файла**.

- **`cat file`** — выводит **весь файл целиком** в терминал. Годится только для маленьких файлов (иначе всё проматывается мгновенно).
- **`less file`** — **постраничный просмотр** с прокруткой: стрелки и `Page Up`/`Page Down` — листать, `/` — поиск, `q` — выход. Не загружает весь файл в память.
- **`head -n 20 file`** — **первые 20 строк** (по умолчанию 10).
- **`tail -n 50 file`** — **последние 50 строк**.

Главный фокус — **`tail -f file.log`**: **«follow»**, следит за файлом и печатает новые строки в реальном времени. Обязательная команда для разглядывания логов в проде. `tail -F` переоткроет файл, если его пересоздаст `logrotate`.',
                'code_example' => '# Целиком, постранично, начало, конец
cat .env
less storage/logs/laravel.log
head -n 5 routes/web.php
tail -n 100 /var/log/nginx/access.log

# Следить за логом в реальном времени и фильтровать ошибки
tail -f storage/logs/laravel.log | grep ERROR',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают chmod и chown?',
                'answer' => 'Две команды для управления **правами доступа** к файлам в Unix.

**`chmod`** меняет **права** (`rwx` = read/write/execute) для **трёх категорий**: владелец / группа / остальные.

Две формы записи:

- **числовая** — `chmod 644 file` (`6` = rw для владельца, `4` = r для группы, `4` = r для остальных)
- **символьная** — `chmod +x script.sh` (добавить execute всем), `chmod u+w file` (владельцу +write)

Типовые значения:

- `644` — обычный файл (`-rw-r--r--`)
- `755` — папка или исполняемый скрипт (`-rwxr-xr-x`)
- `600` — приватный файл (`.env`, ssh-ключи)
- `700` — приватная папка (`~/.ssh`)

**`chown`** меняет **владельца и группу** файла: `chown user:group file`. Чаще нужен `sudo`.

**Зачем это знать:** веб-сервер (`www-data`, `nginx`) не сможет прочитать `.env` или записать в `storage/logs`, если права/владельца настроить неправильно — `403`/`500` ошибки.',
                'code_example' => '# Права
chmod 644 .env              # rw для владельца, r для остальных
chmod 600 ~/.ssh/id_rsa     # только владельцу — обязательно для ssh-ключей
chmod +x deploy.sh          # сделать скрипт исполняемым
chmod -R 775 storage/       # рекурсивно для папок Laravel

# Владелец и группа
sudo chown www-data:www-data storage/ -R
sudo chown $USER:$USER ./project -R   # вернуть себе после Docker

# Посмотреть текущие
ls -la .env
# -rw-r--r-- 1 user user 1.2K Jan 10 12:00 .env',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое sudo простыми словами?',
                'answer' => '**`sudo`** (**s**ubstitute **u**ser **do**) — выполнить **одну команду** с правами другого пользователя, обычно **root**.

Без `sudo` обычный пользователь **не может**:

- ставить системные пакеты (`apt`, `yum`)
- править файлы в `/etc`, `/usr`, `/var`
- слушать порты ниже **1024** (`80`, `443`)
- управлять сервисами через `systemctl`
- читать `/var/log/syslog` и другие защищённые логи

**Полезные формы:**

- `sudo <команда>` — одна команда от root
- `sudo -i` или `sudo -s` — открыть **shell** с правами root
- `sudo -u www-data <команда>` — от имени **другого** пользователя (не root)
- `sudo !!` — повторить **предыдущую** команду через `sudo` (классический фикс «забыл sudo»)

Кто может использовать `sudo` — настраивается в `/etc/sudoers` (правят через `visudo`). По умолчанию пользователи группы `sudo`/`wheel`.',
                'code_example' => '# Поставить пакет
sudo apt install nginx

# Правка системного файла
sudo nano /etc/nginx/nginx.conf

# От имени веб-сервера
sudo -u www-data php artisan queue:work

# Shell от root
sudo -i

# Забыл sudo — повторить через sudo
$ apt install nginx
Permission denied
$ sudo !!
sudo apt install nginx',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают ps и kill?',
                'answer' => 'Две парные команды для **управления процессами**: одна показывает, другая останавливает.

**`ps`** — список запущенных процессов:

- `ps aux` — **все** процессы со столбцами USER/PID/CPU/MEM/COMMAND
- `ps aux | grep php` — найти PHP-процессы
- `ps -ef` — то же в стиле System V
- альтернатива — `top`/`htop` (живой dashboard)

У каждого процесса есть **PID** — числовой идентификатор.

**`kill`** — послать процессу **сигнал**:

- `kill <PID>` — `SIGTERM` (15): **«попроси завершиться»**, процесс может сделать graceful shutdown
- `kill -9 <PID>` — `SIGKILL`: **убить мгновенно**, нельзя перехватить (потеря данных, без cleanup)
- `kill -HUP <PID>` — `SIGHUP` (1): перезагрузить конфиг (nginx, php-fpm)

**`pkill`/`killall`** — убить по имени: `pkill php-fpm`, `killall node`.

**Правило:** сначала `SIGTERM`, и только если не отвечает несколько секунд — `SIGKILL`.',
                'code_example' => '# Найти процесс
ps aux | grep "queue:work"
# user  12345  0.3  1.2  ...  php artisan queue:work

# Дерево процессов
ps auxf
pstree -p

# Аккуратно завершить
kill 12345

# Принудительно, если не реагирует
kill -9 12345

# По имени
pkill -f "queue:work"      # -f ищет по полной команде
killall php-fpm

# Перечитать конфиг nginx без рестарта
sudo kill -HUP $(cat /var/run/nginx.pid)',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое ssh и для чего нужен?',
                'answer' => '**SSH** (**Secure Shell**) — протокол **зашифрованного** входа на удалённый сервер по сети. Стандарт для админки серверов, деплоя и `git` поверх SSH (`git@github.com`).

**Подключение:** `ssh user@server.com` — открывает shell сервера, дальше работаешь, как будто сидишь за ним.

**Аутентификация — по ключам, не по паролю** (так безопаснее):

- генерация: `ssh-keygen -t ed25519 -C "you@example.com"`
- приватный ключ — у тебя: `~/.ssh/id_ed25519` (никому не показывать)
- публичный — на сервере: `~/.ssh/authorized_keys`
- скопировать удобно: `ssh-copy-id user@server.com`

**Что ещё умеет:**

- `ssh user@host "ls -la"` — выполнить **одну команду** удалённо
- `ssh -L 5432:localhost:5432 user@server` — **port forwarding** (открыть удалённую БД на своём `localhost`)
- `scp`/`rsync`/`sftp` — копирование файлов поверх SSH

**Конфиг** `~/.ssh/config` — алиасы для серверов: `ssh prod` вместо `ssh -p 2222 deploy@prod.example.com`.',
                'code_example' => '# Подключение
ssh user@server.com
ssh -p 2222 user@server.com    # нестандартный порт

# Сгенерировать ключ
ssh-keygen -t ed25519 -C "you@example.com"

# Положить публичный ключ на сервер
ssh-copy-id user@server.com

# Одна команда удалённо
ssh user@server "df -h"

# Туннель: локальный 5432 → удалённый Postgres
ssh -L 5432:localhost:5432 user@db.example.com

# ~/.ssh/config — алиасы
# Host prod
#   HostName prod.example.com
#   User deploy
#   Port 2222
#   IdentityFile ~/.ssh/id_ed25519_prod
$ ssh prod',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают scp и rsync?',
                'answer' => 'Две команды для **копирования файлов между серверами через SSH** — но **разные по уму** и применению.

**`scp` (secure copy) — простой:**
- Синтаксис: **`scp file.txt user@server:/path/`** (с `-r` — рекурсивно).
- Передаёт **весь файл** целиком, **без сравнения** с тем, что уже на удалённой стороне.
- **Без дельта-передачи** — повторное копирование одного и того же файла отправляет всё заново.
- **С OpenSSH 9.0** (2022) `scp` deprecated, рекомендуется `sftp` или `rsync`.

**`rsync` — умный:**
- Синтаксис: **`rsync -avz src/ user@server:/dest/`**.
- **Передаёт только различия** (delta — измененные блоки).
- При повторном запуске на тех же файлах **передаёт почти ничего** — отлично для регулярной синхронизации.
- Сохраняет **права, mtime, ownership** (`-a` archive mode).
- **`-z`** — gzip-сжатие в полёте.
- **`-v`** — verbose, **`-P`** — progress bar.

**Полезные флаги `rsync`:**

| Флаг | Что делает |
|---|---|
| **`--delete`** | удалять файлы на приёмнике, которых **нет на источнике** (полный mirror) |
| **`--exclude=pattern`** | исключить файлы/папки (`--exclude=".git"`) |
| **`--exclude-from=file`** | список исключений из файла |
| **`--dry-run`** | показать, что будет сделано, **ничего не копируя** |
| **`--checksum`** | сравнивать **хеши**, а не mtime+size (медленнее, но точнее) |
| **`--bwlimit=1000`** | ограничить bandwidth (KB/s) |

**Типичные применения:**
- **Деплой статики** — `rsync -avz --delete public/ web@srv:/var/www/`.
- **Бэкапы** — `rsync -avz /data/ backup@srv:/backups/$(date +%F)/`.
- **Синхронизация dev / prod** — `rsync -avz --exclude=".env" prod:/app/ local/`.
- **CI/CD** — деплой собранных артефактов.

**Что выбрать:**
- **Один файл, разово** → `scp` (или `sftp` в новом OpenSSH).
- **Папки, регулярно, большие данные** → **`rsync`** (всегда).',
                'difficulty' => 3,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делают wc, sort, uniq?',
                'answer' => 'Три **базовых утилиты** Unix, которые почти всегда работают **в pipe-цепочке** для анализа текста и логов.

**`wc`** (word count) — счётчик:

- `wc -l file` — число **строк**
- `wc -w file` — число **слов**
- `wc -c file` — число **байт**

**`sort`** — сортировка:

- `sort file` — лексикографически
- `sort -n` — **численно** (`10` после `9`, а не до)
- `sort -r` — обратный порядок
- `sort -k2` — по **второй колонке**
- `sort -u` — сортировка + уникальность

**`uniq`** — убирает повторы. **Важное:** убирает **только подряд идущие** дубликаты, поэтому всегда после `sort`.

- `uniq -c` — посчитать сколько раз встретилось

**Классический рецепт:** `sort | uniq -c | sort -rn` — топ-N по количеству.',
                'code_example' => '# Число строк
wc -l routes/web.php

# Топ-10 IP в access.log
cat /var/log/nginx/access.log \
    | awk \'{print $1}\' \
    | sort \
    | uniq -c \
    | sort -rn \
    | head -10
#  1523 192.168.1.10
#   876 10.0.0.5
#   234 8.8.8.8

# Топ-5 кодов ответа
awk \'{print $9}\' access.log | sort | uniq -c | sort -rn | head -5

# Уникальные строки без подсчёта
sort -u file.txt',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое man и --help?',
                'answer' => 'Два способа быстро узнать, **как пользоваться командой**, не выходя из терминала.

**`man <команда>`** открывает встроенную документацию (**man pages**), которая ставится вместе с программой. Внутри:

- стрелки и `Space` — прокрутка
- `/` — поиск
- `n` — следующее совпадение
- `q` — выход

Документация подробная, с описанием всех флагов и примеров.

Если `man` не установлен или это встроенная команда shell — обычно работает **`<команда> --help`** (краткая справка) или `-h`.

Современная альтернатива — утилита **`tldr`** (ставить отдельно), которая выдаёт **короткие практические примеры** вместо длинного описания. Для shell-built-ins (`cd`, `export`) — `help <команда>` в bash.',
                'code_example' => 'man ls          # полная документация
ls --help       # краткая справка
git --help      # справка по git
tldr tar        # практические примеры (если установлен)',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что такое history в shell?',
                'answer' => '**`history`** — встроенная команда shell, которая показывает **список ранее введённых команд** с порядковыми номерами. Используется, чтобы не набирать длинные команды заново.

Способы повторить старую команду:

- `!123` — выполнить команду с номером 123
- `!!` — повторить последнюю (часто как `sudo !!`, если забыл `sudo`)
- `!grep` — повторить последнюю команду, начинавшуюся с `grep`

Главный приём — **`Ctrl+R`**: **интерактивный поиск по истории**. Печатаешь часть команды — shell сразу подставляет совпадение, `Enter` — выполнить, `Ctrl+R` ещё раз — следующее совпадение.

История хранится между сессиями в **`~/.bash_history`** (bash) или **`~/.zsh_history`** (zsh).',
                'code_example' => '$ history | tail -5
  501  composer install
  502  php artisan migrate
  503  php artisan serve
  504  git status
  505  history | tail -5

$ !502           # повторить php artisan migrate
$ !!             # повторить последнюю
$ !php           # последнюю команду, начинавшуюся с php

# Ctrl+R — интерактивный поиск, набери "art" и найдёт artisan',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'devops.tools',
            ],
            [
                'category' => 'DevOps',
                'question' => 'Что делает curl простыми словами?',
                'answer' => '**`curl`** — **консольный HTTP-клиент**. Стандартный инструмент для:

- дебага API (`GET`/`POST`/`PUT`/`DELETE` руками)
- health-чеков в CI/CD и cron-скриптах
- скачивания файлов
- проверки SSL-сертификатов, заголовков, редиректов

**Базовые флаги:**

- `-X POST` — выбрать метод (по умолчанию `GET`)
- `-H "Header: Value"` — добавить заголовок
- `-d \'...\'` — тело запроса
- `-i` — показать **заголовки ответа** + тело
- `-I` — **только заголовки** (`HEAD`-запрос)
- `-v` — verbose, весь handshake и обмен
- `-o file.json` — сохранить ответ в файл
- `-L` — следовать редиректам
- `-u user:pass` — basic auth
- `-w "%{http_code}\\n"` — напечатать только HTTP-код (удобно в скриптах)

Альтернатива — `wget` (скачивание) и `httpie` (более человечный синтаксис).',
                'code_example' => '# GET
curl https://api.example.com/users
curl -i https://api.example.com   # с заголовками
curl -L https://google.com         # следовать редиректам

# POST JSON
curl -X POST https://api.example.com/users \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer eyJ..." \
    -d \'{"name":"Vasya","email":"v@example.com"}\'

# Скачать файл
curl -o backup.sql.gz https://example.com/backup.sql.gz

# Health-check: вернуть только HTTP-код
code=$(curl -s -o /dev/null -w "%{http_code}" https://example.com/healthz)
[ "$code" = "200" ] || exit 1

# Посмотреть только заголовки
curl -I https://github.com',
                'code_language' => 'bash',
                'difficulty' => 2,
                'topic' => 'devops.tools',
            ],
        ];
    }
}
