<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Settings
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'pagination' => [
            'per_page' => 15,
            'max_per_page' => 100,
        ],
        'search' => [
            'fields' => ['name', 'title', 'description', 'email'],
            'min_length' => 3,
        ],
        'sorting' => [
            'default_direction' => 'asc',
            'allowed_directions' => ['asc', 'desc'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Generation Settings
    |--------------------------------------------------------------------------
    */
    'generation' => [
        'force_overwrite' => false,
        'backup_existing' => true,
        'generate_tests' => true,
        'generate_factory' => true,
        'generate_seeder' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Response Format
    |--------------------------------------------------------------------------
    */
    'api' => [
        'format' => [
            'success_key' => 'success',
            'message_key' => 'message',
            'data_key' => 'data',
            'errors_key' => 'errors',
            'status_key' => 'status',
        ],
        'messages' => [
            'created' => 'Resource created successfully',
            'updated' => 'Resource updated successfully',
            'deleted' => 'Resource deleted successfully',
            'retrieved' => 'Resource retrieved successfully',
            'listed' => 'Resources retrieved successfully',
            'not_found' => 'Resource not found',
            'validation_failed' => 'Validation failed',
            'server_error' => 'Internal server error',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Analysis
    |--------------------------------------------------------------------------
    */
    'database' => [
        'excluded_columns' => [
            'id', 'created_at', 'updated_at', 'deleted_at', 'password', 
            'remember_token', 'email_verified_at'
        ],
        'searchable_columns' => [
            'name', 'title', 'description', 'content', 'email', 'slug'
        ],
        'hidden_columns' => [
            'password', 'remember_token', 'api_token'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Code Generation Templates
    |--------------------------------------------------------------------------
    */
    'templates' => [
        'namespace_prefix' => 'App',
        'use_traits' => true,
        'generate_comments' => true,
        'strict_types' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Generation
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'auto_register' => true,
        'prefix' => 'api',
        'middleware' => ['api'],
        'name_prefix' => 'api.',
    ],
];