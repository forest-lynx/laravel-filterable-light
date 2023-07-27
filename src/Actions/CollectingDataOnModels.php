<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Actions;

use ForestLynx\FilterableLight\Exceptions\FileNotFoundException;
use SplFileInfo;
use ReflectionClass;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Model;
use ForestLynx\FilterableLight\Traits\Makeable;
use ForestLynx\FilterableLight\Resolvers\DataModel;
use ForestLynx\FilterableLight\Traits\WithReturnedData;
use ForestLynx\FilterableLight\Exceptions\FilteringNotSupportedException;
use ReflectionException;

class CollectingDataOnModels
{
    use Makeable;
    use WithReturnedData;


    private array $data = [];

    public function __construct(
        private ?string $name = null,
        private ?string $modelsPath = null
    ) {

        $modelsPath = $modelsPath ?: config('filterable.models_path');

        if ($name) {
            $name = correct_model_name($name);
            try {
                $data = DataModel::create(new ReflectionClass($modelsPath . $name))->getData();
                if ($data['filterable']) {
                    $this->data[] = $data;
                }
            } catch (FilteringNotSupportedException $e) {
                //TODO обработка исключений
            } catch (ReflectionException $e) {
                //TODO обработка исключений
            }
        } else {
            $this->data = \collect(File::allFiles(\base_path(\str_replace(["A","\\"], ["a","/"], $modelsPath))))
                ->map(
                    function (SplFileInfo $file) use ($modelsPath) {
                        try {
                            $class = new ReflectionClass($modelsPath . \class_basename($file->getBasename('.php')));

                            if (!$class->isSubclassOf(Model::class)) {
                                return false;
                            }

                            return DataModel::create($class)->getData();
                        } catch (FilteringNotSupportedException $e) {
                            //TODO обработка исключений
                            return null;
                        } catch (ReflectionException $e) {
                            //TODO обработка исключений
                            return null;
                        }
                    }
                )->filter()->values()->toArray();
        }
    }
}
