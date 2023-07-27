<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Traits;

use Illuminate\Support\Collection;

trait WithReturnedData
{
    public function isNotEmptyData(): bool
    {
        return $this->data ? true : false;
    }

    public function getData(): array|Collection
    {
        return $this->isNotEmptyData() ? $this->data : [];
    }
}
