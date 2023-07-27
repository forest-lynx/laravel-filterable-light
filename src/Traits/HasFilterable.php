<?php

declare(strict_types=1);

namespace ForestLynx\FilterableLight\Traits;

use ForestLynx\FilterableLight\Enums\OperatorType;
use ForestLynx\FilterableLight\Exceptions\InvalidOperatorTypeException;
use ForestLynx\FilterableLight\Actions\GeneratingFiltersForModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasFilterable
{
    private array $filters = [];

    public function scopeFilter(Builder $query, Request|array $inputFilters = null): Builder
    {
        $query = $query;

        if ($inputFilters instanceof Request) {
            $inputFilters = $inputFilters->input('filters');
        }

        if (
            (!\is_array($inputFilters) && empty($inputFilters))
            || !$this->getFiltering()
        ) {
            return $query;
        }

        $this->filters = (new GeneratingFiltersForModel(
            $this,
            \undot_recursive($inputFilters)
        ))->generate()->getData();

        if (!$this->filters) {
            return $query;
        }

        $this->addFiltersToQuery($query, $this->filters);

        return $query;
    }

    private function addFiltersToQuery(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $data) {
            match ($key) {
                'related' => $this->queryRelationships($query, $data),
                default => $this->queryField($query, $data, \is_int($key))
            };
        }

        return $query;
    }

    private function queryRelationships(Builder $query, array $data): Builder
    {
        foreach ($data as $relationField => $relationData) {
            if (method_exists($this, $relationField)) {
                $query->whereHas($relationField, function (Builder $relationQuery) use ($relationData) {
                    $this->addFiltersToQuery($relationQuery, $relationData);
                });
            }
        }
        return $query;
    }

    private function queryField(Builder $query, array $data, bool $group = false): Builder
    {
        if ($group) {
            $query->where(function (Builder $q) use ($data) {
                foreach ($data as $key => $d) {
                    $this->queryField($q, $d, is_int($key));
                }
            });
        } else {
            match ($data['operator']) {
                OperatorType::IS_NULL =>
                $query = $query->whereNull(
                    $data['field']
                ),
                OperatorType::IS_NOT_NULL =>
                    $query = $query->whereIsNotNull(
                        $data['field']
                    ),
                OperatorType::BETWEEN =>
                    $query = $query->whereBetween(
                        $data['field'],
                        $data['value']
                    ),
                OperatorType::NOT_BETWEEN =>
                    $query = $query->whereNotBetween(
                        $data['field'],
                        $data['value']
                    ),
                OperatorType::IN =>
                    $query = $query->whereIn(
                        $data['field'],
                        $data['value']
                    ),
                OperatorType::NOT_IN =>
                    $query = $query->whereNotIn(
                        $data['field'],
                        $data['value']
                    ),
                OperatorType::EQUALS,
                OperatorType::NOT_EQUALS,
                OperatorType::LESS_THAN,
                OperatorType::LESS_THAN_OR_EQUAL,
                OperatorType::GREATER_THAN,
                OperatorType::GREATER_THAN_OR_EQUAL,
                OperatorType::LIKE,
                OperatorType::NOT_LIKE =>
                    $query = $query->where(
                        $data['field'],
                        OperatorType::map($data['operator']),
                        $data['value']
                    ),
                default =>
                    throw InvalidOperatorTypeException::create($data['field'], $data['operator'])
            };
        }
        return $query;
    }

    public function getFiltering(): bool
    {
            return !$this->filtering && $this->filtering === false ? false : true;
    }

    public function getFilteringFields(): array
    {
            $skip_fields = \config('filterable-light.skip_fields_default');

            return $this->getFiltering() && $this->filtering_fields
            ? $this->filtering_fields
            : \collect($this->getFillable())
                ->filter(
                    fn (string $field) => !(
                        \in_array($field, $skip_fields, true)
                        || (!\config('filterable.include_fields_related') && \preg_match("/_id/", $field))
                        || \in_array($field, $this->getFilteringFieldsHidden(), true)
                        )
                )->values()->toArray();
    }

    public function getFilteringFieldsHidden(): array
    {
        return $this->filtering_fields_hidden
            ?? [];
    }
}
