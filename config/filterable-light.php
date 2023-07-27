<?php

return [
    'models_path' => env('FILTERABLE_MODELS_PATH', "App\\Models\\"),

    'skip_models' => env('FILTERABLE_SKIP_MODELS', [
        'User',
        'Roles'
    ]),
    'include_fields_related' => env('FILTERABLE_INCLUDE_FIELDS_RELATED', false),
    'skip_fields_default' => env('FILTERABLE_SKIP_FIELDS_DEFAULT', ['id']),
];
