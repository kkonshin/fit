<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\Body\BodyResource;
use App\MoonShine\Resources\Exercise\ExerciseResource;
use App\MoonShine\Resources\Food\FoodResource;
use App\MoonShine\Resources\Image\ImageResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\Goal\GoalResource;
use App\MoonShine\Resources\Expense\ExpenseResource;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  CoreContract<MoonShineConfigurator>  $core
     */
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                BodyResource::class,
                FoodResource::class,
                ImageResource::class,
                ExerciseResource::class,
                GoalResource::class,
                ExpenseResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
            ]);
    }
}
