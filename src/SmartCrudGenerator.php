<?php

namespace Rouangni\SmartCrud;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SmartCrudGenerator
{
    private string $modelName;
    private string $tableName;
    private array $tableColumns = [];
    private array $columnDetails = [];

    public function generate(string $modelName, array $options = []): array
    {
        $this->modelName = $modelName;
        $this->tableName = Str::snake(Str::plural($modelName));

        // Analyze table structure if exists
        if (Schema::hasTable($this->tableName)) {
            $this->analyzeTableStructure();
        }

        $generatedFiles = [];

        // Generate all files
        $generatedFiles['controller'] = $this->generateController();
        $generatedFiles['service'] = $this->generateService();
        $generatedFiles['repository'] = $this->generateRepository();
        $generatedFiles['repository_interface'] = $this->generateRepositoryInterface();
        $generatedFiles['dtos'] = $this->generateDTOs();
        $generatedFiles['requests'] = $this->generateRequests();
        $generatedFiles['resources'] = $this->generateResources();
        $generatedFiles['exception'] = $this->generateException();

        // Register routes if configured
        if (config('smart-crud.routes.auto_register', true) && !($options['no-routes'] ?? false)) {
            $this->registerRoutes();
        }

        return $generatedFiles;
    }

    private function analyzeTableStructure(): void
    {
        $this->tableColumns = Schema::getColumnListing($this->tableName);
        
        // Get detailed column information
        try {
            $columns = DB::select("DESCRIBE {$this->tableName}");
            $this->columnDetails = collect($columns)->keyBy('Field')->toArray();
        } catch (\Exception $e) {
            // Fallback for non-MySQL databases
            $this->columnDetails = [];
        }
    }

    private function generateController(): string
    {
        $stub = $this->getStub('controller');
        $replacements = $this->getControllerReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Http/Controllers/{$this->modelName}Controller.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateService(): string
    {
        $stub = $this->getStub('service');
        $replacements = $this->getServiceReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Services/{$this->modelName}Service.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateRepository(): string
    {
        $stub = $this->getStub('repository');
        $replacements = $this->getRepositoryReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Repositories/{$this->modelName}Repository.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateRepositoryInterface(): string
    {
        $stub = $this->getStub('repository-interface');
        $replacements = $this->getRepositoryInterfaceReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Repositories/Contracts/{$this->modelName}RepositoryInterface.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateDTOs(): array
    {
        $paths = [];
        
        // Create DTO
        $paths['create'] = $this->generateCreateDTO();
        
        // Update DTO
        $paths['update'] = $this->generateUpdateDTO();
        
        // Filter DTO
        $paths['filter'] = $this->generateFilterDTO();
        
        return $paths;
    }

    private function generateCreateDTO(): string
    {
        $stub = $this->getStub('dto-create');
        $replacements = $this->getCreateDTOReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("DTOs/Create{$this->modelName}DTO.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateUpdateDTO(): string
    {
        $stub = $this->getStub('dto-update');
        $replacements = $this->getUpdateDTOReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("DTOs/Update{$this->modelName}DTO.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateFilterDTO(): string
    {
        $stub = $this->getStub('dto-filter');
        $replacements = $this->getFilterDTOReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("DTOs/{$this->modelName}FilterDTO.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateRequests(): array
    {
        $paths = [];
        
        // Store Request
        $paths['store'] = $this->generateStoreRequest();
        
        // Update Request
        $paths['update'] = $this->generateUpdateRequest();
        
        return $paths;
    }

    private function generateStoreRequest(): string
    {
        $stub = $this->getStub('request-store');
        $replacements = $this->getStoreRequestReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Http/Requests/Store{$this->modelName}Request.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateUpdateRequest(): string
    {
        $stub = $this->getStub('request-update');
        $replacements = $this->getUpdateRequestReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Http/Requests/Update{$this->modelName}Request.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateResources(): array
    {
        $paths = [];
        
        // Resource
        $paths['resource'] = $this->generateResource();
        
        // Collection
        $paths['collection'] = $this->generateCollection();
        
        return $paths;
    }

    private function generateResource(): string
    {
        $stub = $this->getStub('resource');
        $replacements = $this->getResourceReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Http/Resources/{$this->modelName}Resource.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateCollection(): string
    {
        $stub = $this->getStub('collection');
        $replacements = $this->getCollectionReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Http/Resources/{$this->modelName}Collection.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function generateException(): string
    {
        $stub = $this->getStub('exception');
        $replacements = $this->getExceptionReplacements();
        
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);
        
        $path = app_path("Exceptions/{$this->modelName}NotFoundException.php");
        $this->ensureDirectoryExists(dirname($path));
        File::put($path, $content);
        
        return $path;
    }

    private function registerRoutes(): void
    {
        $routesPath = base_path('routes/api.php');
        $routeContent = $this->getRouteRegistration();
        
        if (!str_contains(File::get($routesPath), "Route::apiResource('{$this->tableName}'")) {
            File::append($routesPath, $routeContent);
        }
    }

    // Replacement methods
    private function getControllerReplacements(): array
    {
        return [
            '{{ controllerNamespace }}' => 'App\\Http\\Controllers',
            '{{ modelNamespace }}' => "App\\Models\\{$this->modelName}",
            '{{ serviceNamespace }}' => "App\\Services\\{$this->modelName}Service",
            '{{ storeRequestNamespace }}' => "App\\Http\\Requests\\Store{$this->modelName}Request",
            '{{ updateRequestNamespace }}' => "App\\Http\\Requests\\Update{$this->modelName}Request",
            '{{ resourceNamespace }}' => "App\\Http\\Resources\\{$this->modelName}Resource",
            '{{ collectionNamespace }}' => "App\\Http\\Resources\\{$this->modelName}Collection",
            '{{ createDtoNamespace }}' => "App\\DTOs\\Create{$this->modelName}DTO",
            '{{ updateDtoNamespace }}' => "App\\DTOs\\Update{$this->modelName}DTO",
            '{{ filterDtoNamespace }}' => "App\\DTOs\\{$this->modelName}FilterDTO",
            '{{ exceptionNamespace }}' => "App\\Exceptions\\{$this->modelName}NotFoundException",
            '{{ controllerClass }}' => "{$this->modelName}Controller",
            '{{ serviceClass }}' => "{$this->modelName}Service",
            '{{ storeRequestClass }}' => "Store{$this->modelName}Request",
            '{{ updateRequestClass }}' => "Update{$this->modelName}Request",
            '{{ resourceClass }}' => "{$this->modelName}Resource",
            '{{ collectionClass }}' => "{$this->modelName}Collection",
            '{{ createDtoClass }}' => "Create{$this->modelName}DTO",
            '{{ updateDtoClass }}' => "Update{$this->modelName}DTO",
            '{{ filterDtoClass }}' => "{$this->modelName}FilterDTO",
            '{{ exceptionClass }}' => "{$this->modelName}NotFoundException",
            '{{ serviceVariable }}' => Str::camel($this->modelName) . 'Service',
            '{{ modelVariable }}' => Str::camel($this->modelName),
            '{{ resourceVariable }}' => Str::camel(Str::plural($this->modelName)),
        ];
    }

    private function getServiceReplacements(): array
    {
        return [
            '{{ serviceNamespace }}' => 'App\\Services',
            '{{ modelNamespace }}' => "App\\Models\\{$this->modelName}",
            '{{ repositoryInterfaceNamespace }}' => "App\\Repositories\\Contracts\\{$this->modelName}RepositoryInterface",
            '{{ createDtoNamespace }}' => "App\\DTOs\\Create{$this->modelName}DTO",
            '{{ updateDtoNamespace }}' => "App\\DTOs\\Update{$this->modelName}DTO",
            '{{ filterDtoNamespace }}' => "App\\DTOs\\{$this->modelName}FilterDTO",
            '{{ exceptionNamespace }}' => "App\\Exceptions\\{$this->modelName}NotFoundException",
            '{{ serviceClass }}' => "{$this->modelName}Service",
            '{{ modelClass }}' => $this->modelName,
            '{{ repositoryInterfaceClass }}' => "{$this->modelName}RepositoryInterface",
            '{{ createDtoClass }}' => "Create{$this->modelName}DTO",
            '{{ updateDtoClass }}' => "Update{$this->modelName}DTO",
            '{{ filterDtoClass }}' => "{$this->modelName}FilterDTO",
            '{{ exceptionClass }}' => "{$this->modelName}NotFoundException",
            '{{ repositoryVariable }}' => Str::camel($this->modelName) . 'Repository',
            '{{ modelVariable }}' => Str::camel($this->modelName),
        ];
    }

    private function getRepositoryReplacements(): array
    {
        return [
            '{{ repositoryNamespace }}' => 'App\\Repositories',
            '{{ modelNamespace }}' => "App\\Models\\{$this->modelName}",
            '{{ repositoryInterfaceNamespace }}' => "App\\Repositories\\Contracts\\{$this->modelName}RepositoryInterface",
            '{{ filterDtoNamespace }}' => "App\\DTOs\\{$this->modelName}FilterDTO",
            '{{ repositoryClass }}' => "{$this->modelName}Repository",
            '{{ modelClass }}' => $this->modelName,
            '{{ repositoryInterfaceClass }}' => "{$this->modelName}RepositoryInterface",
            '{{ filterDtoClass }}' => "{$this->modelName}FilterDTO",
            '{{ searchFields }}' => $this->generateSearchFields(),
            '{{ sortableFields }}' => $this->generateSortableFields(),
        ];
    }

    private function getRepositoryInterfaceReplacements(): array
    {
        return [
            '{{ repositoryInterfaceNamespace }}' => 'App\\Repositories\\Contracts',
            '{{ modelNamespace }}' => "App\\Models\\{$this->modelName}",
            '{{ filterDtoNamespace }}' => "App\\DTOs\\{$this->modelName}FilterDTO",
            '{{ repositoryInterfaceClass }}' => "{$this->modelName}RepositoryInterface",
            '{{ modelClass }}' => $this->modelName,
            '{{ filterDtoClass }}' => "{$this->modelName}FilterDTO",
        ];
    }

    private function getCreateDTOReplacements(): array
    {
        return [
            '{{ dtoNamespace }}' => 'App\\DTOs',
            '{{ dtoClass }}' => "Create{$this->modelName}DTO",
            '{{ constructorProperties }}' => $this->generateDTOConstructorProperties('create'),
            '{{ fromRequestProperties }}' => $this->generateDTOFromRequestProperties('create'),
            '{{ toArrayProperties }}' => $this->generateDTOToArrayProperties('create'),
        ];
    }

    private function getUpdateDTOReplacements(): array
    {
        return [
            '{{ dtoNamespace }}' => 'App\\DTOs',
            '{{ dtoClass }}' => "Update{$this->modelName}DTO",
            '{{ constructorProperties }}' => $this->generateDTOConstructorProperties('update'),
            '{{ fromRequestProperties }}' => $this->generateDTOFromRequestProperties('update'),
            '{{ toArrayProperties }}' => $this->generateDTOToArrayProperties('update'),
        ];
    }

    private function getFilterDTOReplacements(): array
    {
        return [
            '{{ dtoNamespace }}' => 'App\\DTOs',
            '{{ dtoClass }}' => "{$this->modelName}FilterDTO",
        ];
    }

    private function getStoreRequestReplacements(): array
    {
        return [
            '{{ requestNamespace }}' => 'App\\Http\\Requests',
            '{{ requestClass }}' => "Store{$this->modelName}Request",
            '{{ validationRules }}' => $this->generateValidationRules('create'),
        ];
    }

    private function getUpdateRequestReplacements(): array
    {
        return [
            '{{ requestNamespace }}' => 'App\\Http\\Requests',
            '{{ requestClass }}' => "Update{$this->modelName}Request",
            '{{ validationRules }}' => $this->generateValidationRules('update'),
        ];
    }

    private function getResourceReplacements(): array
    {
        return [
            '{{ resourceNamespace }}' => 'App\\Http\\Resources',
            '{{ resourceClass }}' => "{$this->modelName}Resource",
            '{{ resourceFields }}' => $this->generateResourceFields(),
        ];
    }

    private function getCollectionReplacements(): array
    {
        return [
            '{{ collectionNamespace }}' => 'App\\Http\\Resources',
            '{{ collectionClass }}' => "{$this->modelName}Collection",
            '{{ resourceClass }}' => "{$this->modelName}Resource",
        ];
    }

    private function getExceptionReplacements(): array
    {
        return [
            '{{ exceptionNamespace }}' => 'App\\Exceptions',
            '{{ exceptionClass }}' => "{$this->modelName}NotFoundException",
            '{{ modelClass }}' => $this->modelName,
        ];
    }

    // Generator helper methods
    private function generateSearchFields(): string
    {
        $searchableColumns = collect($this->tableColumns)
            ->intersect(config('smart-crud.database.searchable_columns', []))
            ->map(fn($col) => "                \$q->orWhere('{$col}', 'like', '%' . \$filters->search . '%');")
            ->implode("\n");
            
        return $searchableColumns ?: "                \$q->where('id', '>', 0); // Add searchable fields";
    }

    private function generateSortableFields(): string
    {
        $hiddenColumns = config('smart-crud.database.hidden_columns', []);
        
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $hiddenColumns))
            ->map(fn($col) => "'{$col}'")
            ->implode(', ');
    }

    private function generateDTOConstructorProperties(string $type): string
    {
        $excludedColumns = config('smart-crud.database.excluded_columns', []);
        
        if (empty($this->tableColumns)) {
            // Fallback if no table analysis
            return $this->generateDefaultDTOProperties($type);
        }
        
        if ($type === 'update') {
            // For update DTOs, most fields are optional
            return collect($this->tableColumns)
                ->reject(fn($col) => in_array($col, $excludedColumns))
                ->map(fn($col) => "        public readonly ?{$this->getColumnPHPType($col)} \${$col} = null")
                ->implode(",\n");
        }
        
        // For create DTOs
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $excludedColumns))
            ->map(fn($col) => $this->generateDTOProperty($col))
            ->implode(",\n");
    }

    private function generateDefaultDTOProperties(string $type): string
    {
        // Default properties when no table analysis is available
        if ($type === 'update') {
            return "        public readonly ?string \$name = null,\n        public readonly ?string \$description = null";
        }
        
        return "        public readonly string \$name,\n        public readonly ?string \$description = null";
    }

    private function generateDTOProperty(string $column): string
    {
        $type = $this->getColumnPHPType($column);
        $nullable = $this->isColumnNullable($column) ? '?' : '';
        $default = $this->isColumnNullable($column) ? ' = null' : $this->getDefaultValue($column);
        
        return "        public readonly {$nullable}{$type} \${$column}{$default}";
    }

    private function generateDTOFromRequestProperties(string $type): string
    {
        $excludedColumns = config('smart-crud.database.excluded_columns', []);
        
        if (empty($this->tableColumns)) {
            // Fallback if no table analysis
            return "            name: \$data['name'] ?? '',\n            description: \$data['description'] ?? null";
        }
        
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $excludedColumns))
            ->map(fn($col) => "            {$col}: \$data['{$col}'] ?? " . ($this->isColumnNullable($col) ? 'null' : $this->getDefaultValue($col)))
            ->implode(",\n");
    }

    private function generateDTOToArrayProperties(string $type): string
    {
        $excludedColumns = config('smart-crud.database.excluded_columns', []);
        
        if (empty($this->tableColumns)) {
            // Fallback if no table analysis
            return "            'name' => \$this->name,\n            'description' => \$this->description";
        }
        
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $excludedColumns))
            ->map(fn($col) => "            '{$col}' => \$this->{$col}")
            ->implode(",\n");
    }

    private function generateValidationRules(string $type): string
    {
        $excludedColumns = config('smart-crud.database.excluded_columns', []);
        
        if (empty($this->tableColumns)) {
            // Fallback if no table analysis
            return $this->generateDefaultValidationRules($type);
        }
        
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $excludedColumns))
            ->map(fn($col) => $this->generateFieldValidationRule($col, $type))
            ->implode(",\n");
    }

    private function generateDefaultValidationRules(string $type): string
    {
        if ($type === 'update') {
            return "            'name' => 'sometimes|string|max:255',\n            'description' => 'sometimes|string'";
        }
        
        return "            'name' => 'required|string|max:255',\n            'description' => 'nullable|string'";
    }

    private function generateFieldValidationRule(string $column, string $type): string
    {
        $rules = [];
        
        // Required for create, optional for update
        if ($type === 'create' && !$this->isColumnNullable($column)) {
            $rules[] = 'required';
        } elseif ($type === 'update') {
            $rules[] = 'sometimes';
        }
        
        // Type validation based on column type
        $columnType = $this->getColumnPHPType($column);
        switch ($columnType) {
            case 'int':
                $rules[] = 'integer';
                break;
            case 'float':
                $rules[] = 'numeric';
                break;
            case 'bool':
                $rules[] = 'boolean';
                break;
            default:
                $rules[] = 'string';
                if ($this->getColumnMaxLength($column)) {
                    $rules[] = 'max:' . $this->getColumnMaxLength($column);
                }
                break;
        }
        
        // Special validations
        if (str_contains($column, 'email')) {
            $rules[] = 'email';
            if ($type === 'create') {
                $rules[] = "unique:{$this->tableName},{$column}";
            } else {
                $rules[] = "unique:{$this->tableName},{$column},{\$this->route('id')}";
            }
        }
        
        $ruleString = implode('|', $rules);
        return "            '{$column}' => '{$ruleString}'";
    }

    private function generateResourceFields(): string
    {
        $hiddenColumns = config('smart-crud.database.hidden_columns', []);
        
        if (empty($this->tableColumns)) {
            // Fallback if no table analysis
            return "            'id' => \$this->id,\n            'name' => \$this->name,\n            'description' => \$this->description,\n            'created_at' => \$this->created_at?->format('Y-m-d H:i:s'),\n            'updated_at' => \$this->updated_at?->format('Y-m-d H:i:s')";
        }
        
        return collect($this->tableColumns)
            ->reject(fn($col) => in_array($col, $hiddenColumns))
            ->map(function($column) {
                if (in_array($column, ['created_at', 'updated_at'])) {
                    return "            '{$column}' => \$this->{$column}?->format('Y-m-d H:i:s')";
                }
                return "            '{$column}' => \$this->{$column}";
            })
            ->implode(",\n");
    }

    private function getRouteRegistration(): string
    {
        $prefix = config('smart-crud.routes.prefix', 'api');
        $middleware = json_encode(config('smart-crud.routes.middleware', ['api']));
        
        return "\n\n// {$this->modelName} routes\nRoute::middleware({$middleware})->group(function () {\n    Route::apiResource('{$this->tableName}', App\\Http\\Controllers\\{$this->modelName}Controller::class);\n});";
    }

    // Column analysis helper methods
    private function getColumnPHPType(string $column): string
    {
        if (empty($this->columnDetails) || !isset($this->columnDetails[$column])) {
            return 'string'; // Default fallback
        }
        
        $type = strtolower($this->columnDetails[$column]['Type']);
        
        if (str_contains($type, 'int')) return 'int';
        if (str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double')) return 'float';
        if (str_contains($type, 'bool') || str_contains($type, 'tinyint(1)')) return 'bool';
        if (str_contains($type, 'date') || str_contains($type, 'timestamp')) return 'string';
        
        return 'string';
    }

    private function isColumnNullable(string $column): bool
    {
        if (empty($this->columnDetails) || !isset($this->columnDetails[$column])) {
            return false;
        }
        
        return isset($this->columnDetails[$column]['Null']) && 
               $this->columnDetails[$column]['Null'] === 'YES';
    }

    private function getColumnMaxLength(string $column): ?int
    {
        if (empty($this->columnDetails) || !isset($this->columnDetails[$column])) {
            return null;
        }
        
        $type = $this->columnDetails[$column]['Type'];
        
        // Extract length from type like varchar(255)
        if (preg_match('/\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    private function getDefaultValue(string $column): string
    {
        $type = $this->getColumnPHPType($column);
        
        return match($type) {
            'int' => ' = 0',
            'float' => ' = 0.0',
            'bool' => ' = false',
            default => " = ''"
        };
    }

    // Utility methods
    private function getStub(string $name): string
    {
        $stubPath = __DIR__ . "/Stubs/{$name}.stub";
        
        if (!File::exists($stubPath)) {
            throw new \Exception("Stub file not found: {$stubPath}");
        }
        
        return File::get($stubPath);
    }

    private function ensureDirectoryExists(string $directory): void
    {
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}