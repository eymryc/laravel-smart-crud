<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default API Version
    |--------------------------------------------------------------------------
    |
    | The default API version to use when generating API CRUD files.
    |
    */
    'default_api_version' => 'V1',

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | Configure the base namespaces for different file types.
    |
    */
    'namespaces' => [
        'controllers' => [
            'api' => 'App\\Http\\Controllers\\Api',
            'web' => 'App\\Http\\Controllers\\Web',
        ],
        'requests' => [
            'api' => 'App\\Http\\Requests\\Api',
            'web' => 'App\\Http\\Requests\\Web',
        ],
        'resources' => 'App\\Http\\Resources\\Api',
        'services' => 'App\\Services',
        'repositories' => 'App\\Repositories',
        'dtos' => 'App\\DTOs',
        'exceptions' => 'App\\Exceptions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Configure the base paths for file generation.
    |
    */
    'paths' => [
        'controllers' => [
            'api' => 'Http/Controllers/Api',
            'web' => 'Http/Controllers/Web',
        ],
        'requests' => [
            'api' => 'Http/Requests/Api',
            'web' => 'Http/Requests/Web',
        ],
        'resources' => 'Http/Resources/Api',
        'services' => 'Services',
        'repositories' => 'Repositories',
        'dtos' => 'DTOs',
        'exceptions' => 'Exceptions',
        'views' => 'resources/views',
        'routes' => [
            'api' => 'routes/api',
            'web' => 'routes/web',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Stubs Configuration
    |--------------------------------------------------------------------------
    |
    | Configure which stubs to use. You can override the default stubs
    | by publishing them and modifying the paths here.
    |
    */
    'stubs' => [
        'api' => [
            'controller' => 'Api/controller.api.stub',
            'store_request' => 'Api/request-store.api.stub',
            'update_request' => 'Api/request-update.api.stub',
            'resource' => 'Api/resource.api.stub',
            'collection' => 'Api/collection.api.stub',
            'routes' => 'Routes/api-routes.stub',
        ],
        'web' => [
            'controller' => 'Web/controller.web.stub',
            'store_request' => 'Web/request-store.web.stub',
            'update_request' => 'Web/request-update.web.stub',
            'view_index' => 'Web/view-index.stub',
            'view_create' => 'Web/view-create.stub',
            'view_edit' => 'Web/view-edit.stub',
            'view_show' => 'Web/view-show.stub',
            'routes' => 'Routes/web-routes.stub',
        ],
        'common' => [
            'service' => 'Common/service.stub',
            'repository' => 'Common/repository.stub',
            'repository_interface' => 'Common/repository-interface.stub',
            'create_dto' => 'Common/dto-create.stub',
            'update_dto' => 'Common/dto-update.stub',
            'filter_dto' => 'Common/dto-filter.stub',
            'exception' => 'Common/exception.stub',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Generation Options
    |--------------------------------------------------------------------------
    |
    | Default options for file generation.
    |
    */
    'options' => [
        'force' => false,
        'skip_common' => false,
        'create_routes' => true,
        'create_views' => true,
        'organize_by_entity' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | File Suffixes
    |--------------------------------------------------------------------------
    |
    | Configure suffixes for generated files.
    |
    */
    'suffixes' => [
        'controller' => 'Controller',
        'service' => 'Service',
        'repository' => 'Repository',
        'repository_interface' => 'RepositoryInterface',
        'request' => 'Request',
        'resource' => 'Resource',
        'collection' => 'Collection',
        'dto' => 'DTO',
        'exception' => 'Exception',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to API generation.
    |
    */
    'api' => [
        'route_prefix' => 'api',
        'middleware' => ['api'],
        'response_format' => 'json',
        'pagination' => true,
        'versioning' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Web Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to Web generation.
    |
    */
    'web' => [
        'route_prefix' => '',
        'middleware' => ['web'],
        'layout' => 'layouts.app',
        'pagination' => true,
        'breadcrumbs' => true,
    ],
];