<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Resources\Body\BodyResource;
use App\MoonShine\Resources\Exercise\ExerciseResource;
use App\MoonShine\Resources\Food\FoodResource;
use App\MoonShine\Resources\Goal\GoalResource;
use App\MoonShine\Resources\Image\ImageResource;
use MoonShine\ColorManager\ColorManager;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\MenuManager\MenuItem;

final class MoonShineLayout extends AppLayout
{
    /**
     * @var null|class-string<PaletteContract>
     */
    protected ?string $palette = PurplePalette::class;

    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            MenuItem::make(BodyResource::class, 'Параметры тела')->icon('user'),
            MenuItem::make(FoodResource::class, 'Продукты')->icon('cake'),
            MenuItem::make(ImageResource::class, 'Фотографии')->icon('photo'),
            MenuItem::make(ExerciseResource::class, 'Упражнения')->icon('fire'),
            MenuItem::make(GoalResource::class, 'Цели')->icon('trophy'),
            ...parent::menu(),
        ];
    }

    /**
     * @param  ColorManager  $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }
}
