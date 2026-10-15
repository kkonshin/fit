<?php

namespace App\Providers;

use App\Models\Body;
use App\Models\Exercise;
use App\Models\Expense;
use App\Models\Goal;
use App\Models\Image;
use App\MoonShine\Support\FirstTwentyRecordsCache;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use MoonShine\Laravel\Models\MoonshineUser;
use MoonShine\Laravel\Models\MoonshineUserRole;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->registerFirstTwentyRecordsCacheInvalidation();
    }

    /**
     * @return void
     */
    private function registerFirstTwentyRecordsCacheInvalidation(): void
    {
        foreach ([
            Body::class,
            Exercise::class,
            Expense::class,
            Goal::class,
            Image::class,
            MoonshineUser::class,
            MoonshineUserRole::class,
        ] as $modelClass) {
            foreach (['saved', 'deleted', 'forceDeleted', 'restored'] as $event) {
                Event::listen("eloquent.$event: $modelClass", function () use ($modelClass): void {
                    FirstTwentyRecordsCache::forgetForModel($modelClass);
                });
            }
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
