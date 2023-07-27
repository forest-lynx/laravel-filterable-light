<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Actions;

use ForestLynx\FilterableLight\Enums\OperatorType;
use ForestLynx\FilterableLight\Exceptions\ValidationFieldException;
use ForestLynx\FilterableLight\Resolvers\DataModel;
use ForestLynx\FilterableLight\Traits\WithReturnedData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class GeneratingFiltersForModel
{
    use WithReturnedData;

    private array $data = [];
    private array $availableFilters = [];

    public function __construct(
        private Model $model,
        private array $filtersFromRequest
    ) {
    }

    public function generate(): static
    {
        $this->availableFilters = DataModel::create($this->model)->getData();

        $this->data = [
            ...$this->setFields(\array_diff_key(
                $this->filtersFromRequest,
                \array_flip(['related'])
            ) ?? [], \array_diff_key(
                $this->availableFilters,
                \array_flip(['related'])
            ) ?? []),

            'related' => $this->setRelated($this->filtersFromRequest['related'] ?? [])
        ];

        return $this;
    }

    private function setFields(array $fields, array $available): array
    {
        $ret = [];
        foreach ($fields as $key => $value) {
            $data = [];
            if (\is_int($key)) {
                foreach ($value as $name => $val) {
                    $data = !Arr::has($available, $name) ?: \array_merge(Arr::get($available, $name), $this->formatValueToArray($name, $val));

                    $ret[$key][$name] = $data;
                }
            } else {
                $data = !Arr::has($available, $key) ?: \array_merge(Arr::get($available, $key), $this->formatValueToArray($key, $value));
                $ret[$key] = $data;
            }
        }

        return $ret ?? [];
    }


    private function setRelated(array $related): array
    {
        foreach ($related as $method => $data) {
            $result[$method] = $this->setFields($data, $this->availableFilters['related'][$method]);
        }

        return $result ?? [];
    }

    private function formatValueToArray(string $field, string $value): array
    {
        try {
            $operator = OperatorType::from(\strtok($value, ':'));
        } catch (\Throwable $th) {
            throw ValidationFieldException::create($field, $value);
        }

        switch ($operator) {
            case OperatorType::BETWEEN:
            case OperatorType::NOT_BETWEEN:
                $result = $this->stepTok('&');
                throw_if(empty($result) || count($result) !== 2, ValidationFieldException::create($field, $value));
                $val = $result;
                break;
            case OperatorType::IS_NULL:
            case OperatorType::IS_NOT_NULL:
                $val = null;
                break;
            case OperatorType::LIKE:
            case OperatorType::NOT_LIKE:
                $val = '%' . $this->stepTok(PHP_EOL)[0] . '%';
                break;
            case OperatorType::IN:
            case OperatorType::NOT_IN:
                $val = \explode(',', $this->stepTok(PHP_EOL)[0]);
                break;
            default:
                $val = $this->stepTok(PHP_EOL)[0];
                break;
        };

        return [
            'operator' => $operator,
            'value' => $val ?? null,
        ];
    }

    private function stepTok(string $separator): array
    {
        $returned = [];
        $step = true;
        while ($step !== false) {
            $step = \strtok($separator);
            if ($step) {
                $returned[] = $step;
            }
        }
        return $returned;
    }
}
