<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Exceptions;

use Exception;

class CannotFieldsForFiltering extends Exception
{
    public static function create(string $model): self
    {
        return new self("У модели `$model` отсутствуют допустимые поля для фильтрации.");
    }
}
