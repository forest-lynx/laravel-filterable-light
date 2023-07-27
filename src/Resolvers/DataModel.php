<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Resolvers;

use ForestLynx\FilterableLight\Traits\WithReturnedData;
use ForestLynx\FilterableLight\Exceptions\FilteringNotSupportedException;
use Illuminate\Database\Eloquent\Model;

class DataModel
{
    use WithReturnedData;

    public function __construct(
        public readonly array $data,
    ) {
    }

    public static function create(Model $model): self
    {

        \throw_if(!$model->getFiltering(), FilteringNotSupportedException::create(\class_basename($model)));

        $filteringFields = DataProperties::create($model)->getData();

        $relation_methods = DataMethods::create($model)->getData();

        return new self([
            ...($filteringFields ?? []),
            'related' => $relation_methods ?? null,
        ]);
    }
}
