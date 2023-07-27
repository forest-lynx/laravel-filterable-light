<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Exceptions;

use Exception;

class FilteringNotSupportedException extends Exception
{
    public static function create(string $name): self
    {
        return new self("Фильтрация для - `$name` не поддерживается.");
    }
}
