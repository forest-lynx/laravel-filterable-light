<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
readonly class FilteringBlocked
{
}
