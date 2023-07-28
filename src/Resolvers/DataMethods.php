<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Resolvers;

use ForestLynx\FilterableLight\Attributes\FilteringBlocked;
use ReflectionClass;
use ReflectionAttribute;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\FilterableLight\Traits\WithReturnedData;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionMethod;

class DataMethods
{
    use WithReturnedData;

    public function __construct(
        public array $data,
    ) {
    }

    public static function create(Model $model): self
    {

        $attributeNamespace = "ForestLynx\\FilterableLight\\Attributes";

        $reflection = new ReflectionClass($model);

        $data = \collect($reflection->getMethods())
        ->transform(
            function (ReflectionMethod $method) use ($model) {
                if (
                    !is_a($method->getReturnType()?->getName(), Relation::class, true)
                    || !$method->isPublic()
                ) {
                    return null;
                }

                $filtering_blocked = \collect($method->getAttributes())
                    ->filter(
                        fn (ReflectionAttribute $reflectionAttribute) =>
                            class_exists($reflectionAttribute->getName())
                            && \is_a($reflectionAttribute->getName(), FilteringBlocked::class, true)
                    )->filter();

                if ($filtering_blocked->isNotEmpty()) {
                    return null;
                }

                $instanceMethod = $method->invoke($model->newInstance());
                $related = $instanceMethod->getRelated();
                if (!\method_exists($related, 'getFiltering') || !$related->getFiltering()) {
                        return null;
                }
                return [
                    $method->getName() => DataProperties::create($related)->getData()
                ];
            }
        )->filter()->collapse()->toArray();

        return new self($data);
    }
}
