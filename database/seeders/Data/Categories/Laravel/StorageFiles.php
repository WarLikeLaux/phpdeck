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
                'answer' => 'spatie/laravel-medialibrary - пакет для прикрепления файлов и изображений к Eloquent-моделям. Модель использует трейт InteractsWithMedia, файлы заливаются через $model->addMedia($path)->toMediaCollection("avatars"), хранятся на любом filesystem disk (local, s3, gcs). Возможности: 1) Media collections - группировки файлов по логике (avatars, gallery, documents), с правилами singleFile/multiple. 2) Image conversions - автоматическая генерация thumbnails/превью при загрузке через GD/Imagick (->width(300)->height(300)->sharpen(10)). 3) Responsive images - набор разных размеров для srcset. 4) URL-генератор - $media->getUrl(), getUrl("thumb"), getResponsiveImages(). 5) Custom-properties (мета-данные) на каждом media-объекте. 6) Streaming download - не грузит весь файл в память. Снимает с разработчика ручную работу с file-storage и таблицей media. Pro-версия добавляет UI для админок и Eloquent-relations через morphMany.',
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
                'answer' => 'spatie/laravel-backup - пакет для регулярных бэкапов Laravel-приложения. Что делает: 1) Дампит указанные БД через нативные утилиты (mysqldump, pg_dump, sqlite3 .dump) - быстрее и надёжнее, чем выгрузка через Eloquent. 2) Архивирует выбранные директории (storage/app, public, и любые другие) в zip. 3) Заливает архив на любые filesystem-disks (s3, dropbox, gcs, ftp; обычно настраивают несколько - один локальный + один off-site для DR). 4) Поддерживает шифрование архива (CRYPTO=password). 5) Cleanup-стратегия по возрасту и max размеру (DefaultStrategy: daily/weekly/monthly buckets). 6) Health-check уведомления в почту/Slack/Discord, если бэкапы перестали успешно выполняться. 7) Monitoring: backup:monitor проверяет, что свежий бэкап существует и не сломан. Запускается php artisan backup:run, обычно ставится в schedule.',
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
