<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Concerns;

trait RedirectAfterSaveToIndex
{
    public function getRedirectAfterSave(): ?string
    {
        return $this->getIndexPageUrl();
    }
}
