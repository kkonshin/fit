<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Concerns;

trait RedirectAfterSaveToIndex
{
    /**
     * @return string|null
     */
    public function getRedirectAfterSave(): ?string
    {
        return $this->getIndexPageUrl();
    }
}
