<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Laravel\ApiResources;
use Database\Seeders\Data\Categories\Laravel\Artisan;
use Database\Seeders\Data\Categories\Laravel\Assemble;
use Database\Seeders\Data\Categories\Laravel\AuthAuthorization;
use Database\Seeders\Data\Categories\Laravel\BasicQa;
use Database\Seeders\Data\Categories\Laravel\CacheSession;
use Database\Seeders\Data\Categories\Laravel\Cloze;
use Database\Seeders\Data\Categories\Laravel\Collections;
use Database\Seeders\Data\Categories\Laravel\Controllers;
use Database\Seeders\Data\Categories\Laravel\EloquentAdvanced;
use Database\Seeders\Data\Categories\Laravel\EloquentBasics;
use Database\Seeders\Data\Categories\Laravel\EloquentRelations;
use Database\Seeders\Data\Categories\Laravel\EventsListeners;
use Database\Seeders\Data\Categories\Laravel\InertiaFrontend;
use Database\Seeders\Data\Categories\Laravel\LegacyMigration;
use Database\Seeders\Data\Categories\Laravel\MailNotifications;
use Database\Seeders\Data\Categories\Laravel\Middleware;
use Database\Seeders\Data\Categories\Laravel\MigrationsSeeders;
use Database\Seeders\Data\Categories\Laravel\Misc;
use Database\Seeders\Data\Categories\Laravel\OctaneHorizon;
use Database\Seeders\Data\Categories\Laravel\QueuesJobs;
use Database\Seeders\Data\Categories\Laravel\RequestsValidation;
use Database\Seeders\Data\Categories\Laravel\Routing;
use Database\Seeders\Data\Categories\Laravel\ServiceContainer;
use Database\Seeders\Data\Categories\Laravel\ServiceProviders;
use Database\Seeders\Data\Categories\Laravel\StorageFiles;
use Database\Seeders\Data\Categories\Laravel\Testing;
use Database\Seeders\Data\Categories\Laravel\TypeIn;

class LaravelQuestions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Misc::all(),
            ServiceContainer::all(),
            ServiceProviders::all(),
            Middleware::all(),
            Routing::all(),
            Controllers::all(),
            RequestsValidation::all(),
            EloquentBasics::all(),
            EloquentRelations::all(),
            EloquentAdvanced::all(),
            MigrationsSeeders::all(),
            CacheSession::all(),
            QueuesJobs::all(),
            EventsListeners::all(),
            MailNotifications::all(),
            AuthAuthorization::all(),
            StorageFiles::all(),
            InertiaFrontend::all(),
            OctaneHorizon::all(),
            Artisan::all(),
            Testing::all(),
            ApiResources::all(),
            LegacyMigration::all(),
            Collections::all(),
            BasicQa::all(),
            Cloze::all(),
            TypeIn::all(),
            Assemble::all(),
        );
    }
}
