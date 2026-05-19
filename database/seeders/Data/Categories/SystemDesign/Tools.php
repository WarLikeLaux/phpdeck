<?php

namespace Database\Seeders\Data\Categories\SystemDesign;

class Tools
{
    public static function all(): array
    {
        return [
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое shell простыми словами?',
                'answer' => 'Shell — это программа-оболочка, которая принимает текстовые команды от пользователя и выполняет их в операционной системе. Когда ты открываешь терминал и пишешь ls или git status — ты общаешься с shell, а он уже дёргает нужные программы. Самые популярные на Linux и macOS — bash (Bourne Again Shell, стандарт по умолчанию) и zsh (улучшенный bash, по умолчанию в macOS с 2019). На Windows — PowerShell и cmd. Терминал (terminal/console) и shell — разные вещи: терминал это окно ввода-вывода, shell — программа, которая внутри него работает.',
                'code_example' => '# Узнать какой shell сейчас используется
echo $SHELL          # /bin/bash или /usr/bin/zsh

# Список доступных shell на машине
cat /etc/shells',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают команды cd, ls, pwd?',
                'answer' => 'Это три базовые команды навигации по файловой системе. cd <папка> (change directory) — сменить текущую папку: cd .. — на уровень вверх, cd ~ — в домашнюю, cd / — в корень, cd - — вернуться в предыдущую. ls (list) — показать содержимое папки: ls -l — подробно (права, владелец, размер), ls -a — со скрытыми (которые начинаются с точки), ls -la — всё вместе, ls -lh — размеры в человеко-читаемом виде. pwd (print working directory) — напечатать полный путь к текущей папке, удобно когда заблудился в дереве каталогов.',
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
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое grep простыми словами?',
                'answer' => 'grep — это утилита для поиска строк, которые соответствуют шаблону, внутри файлов или потока ввода. Название от «global regular expression print». Простой случай: grep "TODO" file.php — выводит все строки с TODO. Полезные флаги: -r — рекурсивно по папке, -i — без учёта регистра, -n — показывать номера строк, -v — инвертировать (строки, которые НЕ совпадают), -E — расширенные регулярки, -l — только имена файлов с совпадением. Часто стоит в pipe-цепочках: cat log | grep ERROR. Современные альтернативы — ripgrep (rg) и ag (silver searcher), они быстрее и по умолчанию игнорируют .gitignore.',
                'code_example' => '# Найти TODO в одном файле с номерами строк
grep -n "TODO" app/Models/User.php

# Рекурсивно искать API_KEY в проекте, без учёта регистра
grep -rin "api_key" .

# Поиск в логе через pipe + подсчёт совпадений
tail -f storage/logs/laravel.log | grep --line-buffered ERROR
cat access.log | grep -c "500 "',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое find простыми словами?',
                'answer' => 'Команда для поиска файлов по имени, типу, размеру. find . -name "*.php" — все PHP-файлы рекурсивно с текущей папки. find . -type d -name "logs" — папки с именем logs. find . -mtime -1 — изменённые за последний день.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое pipe (|) в shell простыми словами?',
                'answer' => 'Передаёт вывод одной команды как ввод другой. Пример: ls | grep ".log" — список файлов передать в grep, тот отфильтрует только .log. Несколько pipe-ов можно цепочкой: cat file.log | grep ERROR | wc -l — посчитать число строк с ERROR.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое перенаправление > и >> в shell?',
                'answer' => 'Записывает вывод команды в файл. echo "hi" > log.txt — перезаписывает файл. echo "hi2" >> log.txt — дописывает в конец. 2> errors.txt — перенаправить ОШИБКИ (stderr). &> all.txt — и stdout, и stderr.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое переменные окружения простыми словами?',
                'answer' => 'Глобальные пары «имя=значение», доступные процессам в системе. Примеры: PATH (где искать программы), HOME (домашняя папка), DATABASE_URL (адрес БД). В bash смотреть: echo $HOME. Задать: export DB_HOST=localhost.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое .env файл простыми словами?',
                'answer' => '.env — это текстовый файл в корне проекта со списком переменных окружения для приложения: DB_HOST, APP_KEY, MAIL_PASSWORD и т.п. Каждая строка — пара KEY=VALUE. Главное правило: .env НЕ коммитится в git (он в .gitignore), потому что содержит секреты (пароли БД, токены API, ключи). Вместе с ним коммитится .env.example — шаблон с теми же ключами, но пустыми или demo-значениями, чтобы коллега, клонировав репозиторий, знал какие переменные ему нужно заполнить. В Laravel файл подгружается автоматически на старте через vlucas/phpdotenv, и значения доступны через env("DB_HOST") или config("database.connections.mysql.host"). На stage и prod значения отличаются от dev — это и есть смысл вынесения конфига наружу (12-factor app).',
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
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают cat, less, head, tail?',
                'answer' => 'Это четыре базовых способа посмотреть содержимое текстового файла. cat file — выводит весь файл в терминал сразу, годится только для маленьких файлов (иначе всё проматывается мгновенно). less file — постраничный просмотр с прокруткой: стрелки и Page Up/Down листают, / — поиск, q — выход, не загружает весь файл в память. head -n 20 file — первые 20 строк (по умолчанию 10). tail -n 50 file — последние 50 строк. Главный фокус — tail -f file.log: «follow», следит за файлом и печатает новые строки в реальном времени, обязательная команда для разглядывания логов в проде. tail -F переоткроет файл, если его пересоздаст logrotate.',
                'code_example' => '# Целиком, постранично, начало, конец
cat .env
less storage/logs/laravel.log
head -n 5 routes/web.php
tail -n 100 /var/log/nginx/access.log

# Следить за логом в реальном времени и фильтровать ошибки
tail -f storage/logs/laravel.log | grep ERROR',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают chmod и chown?',
                'answer' => 'chmod меняет права доступа к файлу (rwx — read/write/execute). chmod 644 file — владелец читает/пишет, остальные читают. chmod +x script.sh — добавить право на исполнение. chown user:group file — сменить владельца и группу. Без этих прав веб-сервер может не прочитать файл или PHP-скрипт не запустится.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое sudo простыми словами?',
                'answer' => 'sudo (substitute user do) — выполнить команду с правами другого пользователя, обычно root. sudo apt install nginx — поставить пакет от имени админа. sudo -i — открыть shell с правами root. Нужно потому, что обычный пользователь не может ставить системные пакеты, править /etc, слушать порты ниже 1024.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают ps и kill?',
                'answer' => 'ps — показать запущенные процессы. ps aux — все процессы с подробностями. ps aux | grep php — найти PHP-процессы. У каждого процесса есть PID. kill PID — послать процессу сигнал SIGTERM (попросить завершиться). kill -9 PID — SIGKILL, убить принудительно (не даёт graceful shutdown). pkill php-fpm — убить по имени.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое ssh и для чего нужен?',
                'answer' => 'SSH (Secure Shell) — протокол для безопасного входа на удалённый сервер по сети. ssh user@server.com — подключиться. Внутри уже доступен shell сервера. Аутентификация чаще по ключам (id_rsa/id_ed25519): публичный ключ кладёшь в ~/.ssh/authorized_keys на сервере, приватный держишь у себя. Пароли отключают как менее безопасные.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают scp и rsync?',
                'answer' => 'scp — копирование файлов через SSH: scp file.txt user@server:/path/. Простой, но без дельта-передачи. rsync — умнее: rsync -avz src/ user@server:/dest/ — передаёт только различия (delta), сохраняет права/время, умеет --delete (удалять лишнее на стороне получателя), --exclude (исключения). Стандарт для деплоя статики и бэкапов.',
                'difficulty' => 3,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают wc, sort, uniq?',
                'answer' => 'wc — счётчик: wc -l file — число строк, wc -w — число слов. sort — сортировка: sort file, sort -n — численно, sort -r — обратно. uniq убирает дубликаты, но только подряд идущие — поэтому обычно sort | uniq. Пример: cat access.log | awk \'{print $1}\' | sort | uniq -c | sort -rn — топ IP-адресов в логе.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое man и --help?',
                'answer' => 'Это два способа быстро узнать, как пользоваться командой, не выходя из терминала. man <команда> открывает встроенную документацию (man pages), которая ставится вместе с программой. Внутри: стрелки и Space — прокрутка, / — поиск, n — следующее совпадение, q — выход. Документация подробная, с описанием всех флагов и примеров. Если man не установлен или это встроенная команда shell — обычно работает <команда> --help (краткая справка) или -h. Современная альтернатива — утилита tldr (надо ставить отдельно), которая выдаёт короткие практические примеры вместо длинного описания. Для shell-built-ins (cd, export) — help <команда> в bash.',
                'code_example' => 'man ls          # полная документация
ls --help       # краткая справка
git --help      # справка по git
tldr tar        # практические примеры (если установлен)',
                'code_language' => 'bash',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое history в shell?',
                'answer' => 'history — встроенная команда shell, которая показывает список ранее введённых команд с порядковыми номерами. Используется, чтобы не набирать длинные команды заново. Способы повторить старую команду: !123 — выполнить команду с номером 123, !! — повторить последнюю (часто используется как sudo !! если забыл sudo), !grep — повторить последнюю команду, начинавшуюся с grep. Главный приём — Ctrl+R: интерактивный поиск по истории, печатаешь часть команды и shell сразу подставляет совпадение, Enter — выполнить, Ctrl+R ещё раз — следующее совпадение. История хранится между сессиями в ~/.bash_history (bash) или ~/.zsh_history (zsh).',
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
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делает curl простыми словами?',
                'answer' => 'curl — консольный HTTP-клиент. curl https://api.example.com — GET-запрос. curl -X POST -H "Content-Type: application/json" -d \'{"name":"Vasya"}\' https://api.example.com/users — POST с JSON. -i показывает заголовки ответа, -v подробный лог, -o file.json сохраняет ответ в файл. Стандартный инструмент для дебага API и health-чеков в скриптах.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
        ];
    }
}
