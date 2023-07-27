<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Resolvers;

use ForestLynx\FilterableLight\Exceptions\CannotFieldsForFiltering;
use ForestLynx\FilterableLight\Traits\WithReturnedData;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

class DataProperties
{
    use WithReturnedData;

    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function create(Model $model): self
    {

        $properties = $model->getFilteringFields();
        \throw_if(empty($properties), CannotFieldsForFiltering::create(\class_basename($model)));


        $timestamps = $model->timestamps
            ? [
                $model->getCreatedAtColumn(),
                $model->getUpdatedAtColumn()
            ]
            : [];
        $softDeletes = \in_array(SoftDeletes::class, (new ReflectionClass($model))->getTraitNames())
            ? (array) $model->getDeletedAtColumn()
            : [];

        $properties = \array_merge($properties, $timestamps, $softDeletes);

        $properties = \array_diff($properties, \config('filterable-light.skip_fields_default'));

        foreach ($properties as $key => $name) {
            $data[$name] = [
                'field' => $name
            ];
        }

        return new self($data);
    }
}
