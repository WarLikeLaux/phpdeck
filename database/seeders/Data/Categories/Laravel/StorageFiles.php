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
                'answer' => 'File Storage - абстракция над файловыми хранилищами через Flysystem. Disks: local (storage/app), public (storage/app/public, доступен через symlink), s3 (Amazon S3), ftp, sftp. Один интерфейс - разные хранилища.',
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
                'answer' => 'Spatie Media Library — пакет для прикрепления файлов и изображений к Eloquent-моделям. Модель использует трейт InteractsWithMedia, файлы заливаются через addMedia()->toMediaCollection(), хранятся на любом filesystem disk. Поддерживает media collections, конверсии (thumbnails, resize) через image-driver и ответственный URL-генератор. Снимает с разработчика ручную работу с file-storage и таблицами медиа.',
                'difficulty' => 3,
                'topic' => 'laravel.storage_files',
            ],
            [
                'category' => 'Laravel',
                'question' => 'Что такое Spatie Laravel-Backup и для чего он применяется?',
                'answer' => 'Spatie Laravel-Backup — пакет для регулярного бэкапа приложения: дампит указанные базы (mysqldump/pg_dump), архивирует выбранные директории, заливает на любой filesystem disk (S3, Dropbox, локально). Поддерживает шифрование, ротацию по возрасту/размеру и health-check уведомления в почту/Slack. Запускается артизан-командой backup:run и обычно ставится в schedule.',
                'difficulty' => 3,
                'topic' => 'laravel.storage_files',
            ],
        ];
    }
}
