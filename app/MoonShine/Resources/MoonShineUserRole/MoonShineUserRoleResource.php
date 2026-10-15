<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\MoonShineUserRole;

use App\MoonShine\Resources\CachedModelResource;
use App\MoonShine\Resources\Concerns\RedirectAfterSaveToIndex;
use App\MoonShine\Resources\MoonShineUserRole\Pages\MoonShineUserRoleFormPage;
use App\MoonShine\Resources\MoonShineUserRole\Pages\MoonShineUserRoleIndexPage;
use MoonShine\Laravel\Models\MoonshineUserRole;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends CachedModelResource<MoonshineUserRole, MoonShineUserRoleIndexPage, MoonShineUserRoleFormPage, null>
 */
#[Icon('bookmark')]
#[Group('moonshine::ui.resource.system', 'users', translatable: true)]
#[Order(1)]
class MoonShineUserRoleResource extends CachedModelResource
{
    use RedirectAfterSaveToIndex;

    protected string $model = MoonshineUserRole::class;

    protected string $column = 'name';

    protected int $itemsPerPage = 10;

    protected bool $createInModal = true;

    protected bool $detailInModal = true;

    protected bool $editInModal = true;

    protected bool $cursorPaginate = true;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return __('moonshine::ui.resource.role');
    }

    /**
     * @return ListOf
     */
    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    /**
     * @return string[]
     */
    protected function pages(): array
    {
        return [
            MoonShineUserRoleIndexPage::class,
            MoonShineUserRoleFormPage::class,
        ];
    }

    /**
     * @return string[]
     */
    protected function search(): array
    {
        return [
            'id',
            'name',
        ];
    }
}
