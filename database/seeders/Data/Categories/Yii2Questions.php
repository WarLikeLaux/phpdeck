<?php

namespace Database\Seeders\Data\Categories;

use Database\Seeders\Data\Categories\Yii2\ActiveForm;
use Database\Seeders\Data\Categories\Yii2\ActiveRecord;
use Database\Seeders\Data\Categories\Yii2\AssetBundles;
use Database\Seeders\Data\Categories\Yii2\Authentication;
use Database\Seeders\Data\Categories\Yii2\Authorization;
use Database\Seeders\Data\Categories\Yii2\Basics;
use Database\Seeders\Data\Categories\Yii2\Behaviors;
use Database\Seeders\Data\Categories\Yii2\Cache;
use Database\Seeders\Data\Categories\Yii2\Components;
use Database\Seeders\Data\Categories\Yii2\Console;
use Database\Seeders\Data\Categories\Yii2\Controllers;
use Database\Seeders\Data\Categories\Yii2\DataProvider;
use Database\Seeders\Data\Categories\Yii2\DebugScenarios;
use Database\Seeders\Data\Categories\Yii2\Di;
use Database\Seeders\Data\Categories\Yii2\Errors;
use Database\Seeders\Data\Categories\Yii2\Events;
use Database\Seeders\Data\Categories\Yii2\Filters;
use Database\Seeders\Data\Categories\Yii2\Gii;
use Database\Seeders\Data\Categories\Yii2\I18n;
use Database\Seeders\Data\Categories\Yii2\Logging;
use Database\Seeders\Data\Categories\Yii2\Mail;
use Database\Seeders\Data\Categories\Yii2\Migrations;
use Database\Seeders\Data\Categories\Yii2\Misc;
use Database\Seeders\Data\Categories\Yii2\Modules;
use Database\Seeders\Data\Categories\Yii2\QueryBuilder;
use Database\Seeders\Data\Categories\Yii2\Refactoring;
use Database\Seeders\Data\Categories\Yii2\Relations;
use Database\Seeders\Data\Categories\Yii2\Requests;
use Database\Seeders\Data\Categories\Yii2\Rest;
use Database\Seeders\Data\Categories\Yii2\Routing;
use Database\Seeders\Data\Categories\Yii2\Security;
use Database\Seeders\Data\Categories\Yii2\Testing;
use Database\Seeders\Data\Categories\Yii2\Validators;
use Database\Seeders\Data\Categories\Yii2\Views;

class Yii2Questions
{
    /**
     * @return array<int, array{category: string, question: string, answer: string, code_example?: ?string, code_language?: ?string, cloze_text?: ?string, short_answer?: ?string, assemble_chunks?: ?array<int, string>, difficulty?: int, topic?: string}>
     */
    public static function all(): array
    {
        return array_merge(
            Basics::all(),
            Components::all(),
            Events::all(),
            Behaviors::all(),
            Di::all(),
            Controllers::all(),
            Routing::all(),
            Requests::all(),
            Validators::all(),
            ActiveForm::all(),
            Views::all(),
            AssetBundles::all(),
            Modules::all(),
            ActiveRecord::all(),
            Relations::all(),
            QueryBuilder::all(),
            Migrations::all(),
            DataProvider::all(),
            Authentication::all(),
            Authorization::all(),
            Filters::all(),
            Rest::all(),
            Security::all(),
            Cache::all(),
            Logging::all(),
            Errors::all(),
            I18n::all(),
            Mail::all(),
            Console::all(),
            Gii::all(),
            Testing::all(),
            Refactoring::all(),
            DebugScenarios::all(),
            Misc::all(),
        );
    }
}
