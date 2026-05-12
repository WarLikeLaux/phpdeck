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
                'question' => 'Что такое контейнер и чем он отличается от виртуальной машины?',
                'answer' => 'Контейнер — изолированный процесс, использующий ядро хост-ОС, но со своими файловой системой, сетью и зависимостями. Лёгкий (десятки МБ, секунды запуска). ВМ — полная ОС со своим ядром (гигабайты, минуты). Контейнер не «компьютер в компьютере», как ВМ — это просто хорошо упакованный процесс.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое образ (image) Docker?',
                'answer' => 'Шаблон, по которому создаётся контейнер. Слоёная структура: базовый образ (например, php:8.3) + слои с твоим кодом и зависимостями. Из одного образа можно создать сколько угодно одинаковых контейнеров. Образы хранятся в registry (Docker Hub, GitHub Container Registry).',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
            [
                'category' => 'Архитектура систем',
                'question' => 'Что такое Dockerfile простыми словами?',
                'answer' => 'Текстовый файл-рецепт сборки образа. Описывает шаги: FROM (базовый образ), COPY (скопировать файлы), RUN (выполнить команду), CMD (что запускать в контейнере). docker build -t myapp . собирает образ по этому файлу.',
                'difficulty' => 2,
                'topic' => 'system_design.tools',
            ],
        ];
    }
}
