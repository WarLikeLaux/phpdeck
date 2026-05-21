<?php

namespace Database\Seeders\Data\Categories\Laravel;

class StorageFiles
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return [
            [
                'category' => 'Laravel',
                'question' => 'Что такое File Storage и какие есть disks?',
                'answer' => '**File Storage** — абстракция Laravel над файловыми хранилищами поверх библиотеки **Flysystem**. Единый API для разных бэкендов.

Стандартные disks (`config/filesystems.php`):

- **`local`** — `storage/app`. **Приватные** файлы (не доступны из браузера).
- **`public`** — `storage/app/public`. Публичные файлы, **доступны через симлинк** `public/storage` → `storage/app/public`.
- **`s3`** — Amazon S3 / S3-совместимое хранилище (MinIO, Yandex Object Storage).
- **`ftp`**, **`sftp`** — удалённый сервер.

Использование:

- **`Storage::put($path, $content)`** — сохранить.
- **`Storage::get($path)`** — прочитать.
- **`Storage::disk(\'s3\')->put(...)`** — на конкретный disk.
- **`Storage::url($path)`** — публичный URL (только для `public`/`s3`).
- **`Storage::delete($path)`** — удалить.

Чтобы `public` disk стал доступен через `/storage/...`, нужно один раз создать симлинк: **`php artisan storage:link`**.',
                'code_example' => 'Storage::disk(\'s3\')->put(\'avatars/1.jpg\', $contents);
$url = Storage::disk(\'public\')->url(\'avatars/1.jpg\');
$content = Storage::get(\'file.txt\');
Storage::delete(\'file.txt\');

// Создать симлинк public
php artisan storage:link',
                'code_language' => 'php',
                'difficulty' => 2,
                'topic' => 'laravel.storage_files',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Spatie Media Library и какие её возможности?',
                'answer' => '**`spatie/laravel-medialibrary`** — пакет для прикрепления файлов и изображений **к Eloquent-моделям**. Модель использует трейт `InteractsWithMedia`, файлы заливаются через `$model->addMedia($path)->toMediaCollection(\'avatars\')`, хранятся на любом filesystem disk (`local`, `s3`, `gcs`).

**Ключевые возможности:**

| Фича | Что даёт |
| --- | --- |
| **Media collections** | Группировки файлов (`avatar`, `gallery`, `documents`); правила `singleFile()` / multiple |
| **Image conversions** | Автоматические thumbnails/превью при загрузке через GD/Imagick (`->width(300)->sharpen(10)`) |
| **Responsive images** | Набор разных размеров для `srcset` |
| **URL-генератор** | `$media->getUrl()`, `getUrl(\'thumb\')`, `getResponsiveImages()` |
| **Custom properties** | Метаданные на каждом media-объекте |
| **Streaming download** | Не грузит весь файл в память |
| **Очереди** | Конверсии можно генерировать в фоне (`Queueable` по умолчанию) — или `->nonQueued()` для синхронных |

**Связи с Eloquent:** под капотом `morphMany` к таблице `media`. Можно фильтровать, сортировать, eager-load.

**Когда брать:** нужен **готовый аплоадер** с превью, без ручной работы с `Storage::put` + таблицами + ресайзом. Pro-версия добавляет UI-компоненты для админок.

**Альтернатива для простых кейсов:** связка `Intervention/Image` + `Storage::put()` + миграция `media` своими руками — меньше абстракций, но больше кода.',
                'code_example' => '<?php
// composer require spatie/laravel-medialibrary
// php artisan vendor:publish --provider="Spatie\\MediaLibrary\\MediaLibraryServiceProvider" --tag="migrations"
// php artisan migrate

use Spatie\\MediaLibrary\\HasMedia;
use Spatie\\MediaLibrary\\InteractsWithMedia;
use Spatie\\MediaLibrary\\MediaCollections\\Models\\Media;

class User extends Model implements HasMedia {
    use InteractsWithMedia;

    public function registerMediaCollections(): void {
        $this->addMediaCollection("avatar")->singleFile();         // только одна аватарка
        $this->addMediaCollection("gallery");                       // много файлов
    }

    public function registerMediaConversions(Media $media = null): void {
        $this->addMediaConversion("thumb")
            ->width(200)->height(200)
            ->sharpen(10)
            ->nonQueued();                                          // синхронно при загрузке

        $this->addMediaConversion("preview")
            ->width(800)->height(600)
            ->performOnCollections("gallery");
    }
}

// Загрузка
$user->addMediaFromRequest("avatar")
    ->withCustomProperties(["uploaded_from" => "mobile"])
    ->toMediaCollection("avatar");

// URL
$user->getFirstMediaUrl("avatar");                  // оригинал
$user->getFirstMediaUrl("avatar", "thumb");         // конверсия 200x200

// Удаление
$user->clearMediaCollection("gallery");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.storage_files',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Spatie Laravel-Backup и для чего он применяется?',
                'answer' => '**`spatie/laravel-backup`** — пакет для **регулярных бэкапов** Laravel-приложения.

**Что делает:**

- **Дампит БД** через **нативные утилиты** (`mysqldump`, `pg_dump`, `sqlite3 .dump`) — быстрее и надёжнее, чем выгрузка через Eloquent.
- **Архивирует директории** (`storage/app`, `.env`, любые свои) в **zip**.
- **Заливает архив** на любые filesystem disks (`s3`, `gcs`, `ftp`, `dropbox`). Типовая схема — **локальный** + **off-site** диск для disaster recovery.
- **Шифрует** архив паролем (`CRYPTO`).

**Cleanup-стратегия** (`DefaultStrategy`):

- Возрастные **«корзины»** — daily / weekly / monthly / yearly.
- Хранит свежие, прорежает старые: «все за 7 дней, ежедневные за 16, еженедельные за 8 недель…».
- Лимит по объёму — «удалять старое, если суммарный размер > N МБ».

**Мониторинг и алерты:**

- **`backup:monitor`** проверяет, что свежий бэкап **существует и не сломан**.
- **Notifications** в почту/Slack/Discord при `BackupHasFailed`, `BackupHasMissed`, `UnhealthyBackupWasFound`.

**Команды:**

- `php artisan backup:run` — сделать бэкап.
- `php artisan backup:clean` — почистить старые.
- `php artisan backup:monitor` — проверить здоровье.
- `php artisan backup:list` — что лежит на дисках.

**В `routes/console.php`** ставят все три на расписание — `daily()` с разными часами.',
                'code_example' => '<?php
// composer require spatie/laravel-backup
// php artisan vendor:publish --provider="Spatie\\Backup\\BackupServiceProvider"

// config/backup.php
return [
    "backup" => [
        "name" => env("APP_NAME", "laravel-backup"),
        "source" => [
            "files" => [
                "include" => [base_path("storage/app"), base_path(".env")],
                "exclude" => [base_path("vendor"), base_path("node_modules")],
            ],
            "databases" => ["mysql"],
        ],
        "destination" => [
            "disks" => ["local", "s3-backups"],
            "filename_prefix" => "",
            "compression_method" => ZipArchive::CM_DEFLATE,
        ],
    ],

    "cleanup" => [
        "default_strategy" => [
            "keep_all_backups_for_days"             => 7,
            "keep_daily_backups_for_days"           => 16,
            "keep_weekly_backups_for_weeks"         => 8,
            "keep_monthly_backups_for_months"       => 4,
            "keep_yearly_backups_for_years"         => 2,
            "delete_oldest_backups_when_using_more_megabytes_than" => 5000,
        ],
    ],

    "notifications" => [
        "mail" => ["to" => "ops@example.com"],
        "slack" => ["webhook_url" => env("BACKUP_SLACK")],
    ],
];

// routes/console.php (Laravel 11+)
Schedule::command("backup:clean")->daily()->at("01:00");
Schedule::command("backup:run")->daily()->at("01:30");
Schedule::command("backup:monitor")->daily()->at("06:00");',
                'code_language' => 'php',
                'difficulty' => 3,
                'topic' => 'laravel.storage_files',
            ],
        ];
    }
}
