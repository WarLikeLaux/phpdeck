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
                'answer' => 'Программа-командная оболочка, которая принимает текстовые команды и выполняет их в ОС. Самые популярные на Linux/macOS — bash и zsh. На Windows — PowerShell, cmd. Когда пишешь команды в терминале, ты общаешься с shell.',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают команды cd, ls, pwd?',
                'answer' => 'cd <папка> — сменить текущую папку (cd .. — вверх, cd ~ — домой, cd / — в корень). ls — показать содержимое (ls -la — со скрытыми и подробно). pwd — напечатать текущий путь (где я сейчас).',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое grep простыми словами?',
                'answer' => 'Команда для поиска текста в файлах. grep "TODO" file.php — найти все строки с TODO. grep -r "API_KEY" . — рекурсивно искать в текущей папке. grep -i — без учёта регистра. grep -n — с номерами строк.',
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
                'answer' => 'Текстовый файл с переменными окружения для приложения: DB_HOST=localhost, APP_KEY=.... Не коммитится в git (секреты). Загружается приложением при старте — в Laravel автоматически. .env.example — шаблон, коммитится.',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что делают cat, less, head, tail?',
                'answer' => 'cat file — напечатать весь файл в терминал (для маленьких). less file — постранично с прокруткой (q — выход, / — поиск). head -n 20 file — первые 20 строк. tail -n 50 file — последние 50. tail -f file.log — «следить» за файлом в реальном времени, удобно для логов.',
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
                'answer' => 'man <команда> — открыть встроенную документацию (man pages). Например, man ls. Внутри: стрелки/Space — прокрутка, / — поиск, q — выход. Если man нет — обычно работает <команда> --help (краткая справка) или -h. Также tldr (внешняя утилита) даёт короткие практические примеры вместо длинного man.',
                'difficulty' => 1,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое history в shell?',
                'answer' => 'history — показать список ранее введённых команд с номерами. !123 — повторить команду с номером 123. !! — повторить последнюю. Ctrl+R — интерактивный поиск по истории (начни печатать — найдёт совпадение). Команды хранятся в ~/.bash_history или ~/.zsh_history. Полезно для повторения длинных команд без повторного набора.',
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
