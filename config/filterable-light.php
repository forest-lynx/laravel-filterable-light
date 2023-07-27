<?php

return [
    'models_path' => env('FILTERABLE_L_MODELS_PATH', "App\\Models\\"),

    'skip_models' => env('FILTERABLE_L_SKIP_MODELS', [
        'User',
        'Roles'
    ]),
    'include_fields_related' => env('FILTERABLE_L_INCLUDE_FIELDS_RELATED', false),
    'skip_fields_default' => env('FILTERABLE_L_SKIP_FIELDS_DEFAULT', ['id']),
];
