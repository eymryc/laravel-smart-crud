<?php

namespace Rouangni\SmartCrud\Generators;

use Illuminate\Support\Str;
use Rouangni\SmartCrud\Contracts\CrudGeneratorInterface;

class ApiCrudGenerator extends AbstractGenerator implements CrudGeneratorInterface
{
    /**
     * Generate API CRUD file
     */
    public function generate(string $model, string $type, array $options = []): bool
    {
        $method = 'generate' . $type;
        
        if (method_exists($this, $method)) {
            return $this->$method($model, $options);
        }
        
        throw new \InvalidArgumentException("Unknown API generator type: {$type}");
    }

    /**
     * Generate all API CRUD files
     */
    public function generateAll(string $model, array $options = []): array
    {
        $results = [];
        $types = ['Controller', 'StoreRequest', 'UpdateRequest', 'Resource', 'Collection'];
        
        if (!($options['skip_routes'] ?? false)) {
            $types[] = 'Routes';
        }

        foreach ($types as $type) {
            $results[$type] = $this->generate($model, $type, $options);
        }

        return $results;
    }

    /**
     * Generate API Controller (interface implementation)
     */
    public function generateController(string $model, array $options = []): bool
    {
        return $this->generateControllerFile($model, $options);
    }

    /**
     * Generate API requests
     */
    public function generateRequests(string $model, array $options = []): bool
    {
        $storeResult = $this->generateStoreRequest($model, $options);
        $updateResult = $this->generateUpdateRequest($model, $options);
        
        return $storeResult && $updateResult;
    }

    /**
     * Generate API routes (interface implementation)
     */
    public function generateRoutes(string $model, array $options = []): bool
    {
        return $this->generateRoutesFile($model, $options);
    }

    /**
     * Generate API Controller
     */
    protected function generateControllerFile(string $model, array $options): bool
    {
        $version = $options['api-version'] ?? $this->config('default_api_version', 'V1');
        $namespace = $this->getNamespace($model, 'Controller', $options);
        $className = "{$model}Controller";
        $filePath = $this->getFilePath($model, 'Controller', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
            'version' => $version,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.controller'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate API Store Request
     */
    protected function generateStoreRequest(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'StoreRequest', $options);
        $className = "Store{$model}Request";
        $filePath = $this->getFilePath($model, 'StoreRequest', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.store_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate API Update Request
     */
    protected function generateUpdateRequest(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'UpdateRequest', $options);
        $className = "Update{$model}Request";
        $filePath = $this->getFilePath($model, 'UpdateRequest', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.update_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate API Resource
     */
    protected function generateResource(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'Resource', $options);
        $className = "{$model}Resource";
        $filePath = $this->getFilePath($model, 'Resource', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.resource'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate API Collection
     */
    protected function generateCollection(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'Collection', $options);
        $className = "{$model}Collection";
        $filePath = $this->getFilePath($model, 'Collection', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
            'resource' => "{$model}Resource",
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.collection'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate API Routes
     */
    protected function generateRoutesFile(string $model, array $options): bool
    {
        $version = $options['api-version'] ?? $this->config('default_api_version', 'V1');
        $apiRoutesFile = $this->basePath('routes/api.php');

        // Create routes/api.php if it doesn't exist
        if (!$this->files->exists($apiRoutesFile)) {
            $this->createApiRoutesFile($apiRoutesFile);
        }

        $routeContent = $this->generateApiRouteContent($model, $version, $options);
        
        return $this->appendToRoutesFile($apiRoutesFile, $routeContent, $model, 'API');
    }

    /**
     * Create initial API routes file
     */
    protected function createApiRoutesFile(string $filePath): void
    {
        $content = "<?php\n\nuse Illuminate\Http\Request;\nuse Illuminate\Support\Facades\Route;\n\n";
        $content .= "Route::get('/user', function (Request \$request) {\n";
        $content .= "    return \$request->user();\n";
        $content .= "})->middleware('auth:sanctum');\n\n";
        $content .= "// ===== Smart CRUD Generated Routes =====\n";
        
        $this->files->put($filePath, $content);
    }

    /**
     * Generate API route content for a model
     */
    protected function generateApiRouteContent(string $model, string $version, array $options): string
    {
        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'controller' => "{$model}Controller",
            'controllerNamespace' => $this->getNamespace($model, 'Controller', $options),
            'version' => $version,
            'versionLower' => strtolower($version),
        ]);

        $stubContent = $this->files->get($this->getStubPath($this->config('stubs.api.routes')));
        return $this->replacePlaceholders($stubContent, $replacements);
    }

    /**
     * Append route content to existing routes file
     */
    protected function appendToRoutesFile(string $filePath, string $routeContent, string $model, string $type): bool
    {
        $existingContent = $this->files->get($filePath);
        
        // Check if routes for this model already exist
        $modelKebab = Str::kebab(Str::plural($model));
        if (strpos($existingContent, "'{$modelKebab}'") !== false || 
            strpos($existingContent, "\"{$modelKebab}\"") !== false) {
            $this->info("Routes for {$model} already exist in {$type} routes file");
            return false;
        }

        $newContent = $this->insertRouteContent($existingContent, $routeContent, $model, $type);
        
        $this->files->put($filePath, $newContent);
        $this->info("Added {$type} routes for {$model}");
        
        return true;
    }

    /**
     * Insert route content at the appropriate place
     */
    protected function insertRouteContent(string $existingContent, string $routeContent, string $model, string $type): string
    {
        $lines = explode("\n", $existingContent);
        $newLines = [];
        $importsSection = true;
        
        foreach ($lines as $line) {
            $newLines[] = $line;
            
            // Add import after existing use statements
            if ($importsSection && trim($line) && 
                !str_starts_with(trim($line), 'use ') && 
                !str_starts_with(trim($line), '<?php') &&
                !empty(trim($line))) {
                
                // Add the import for this controller
                $controllerImport = "use " . $this->getNamespace($model, 'Controller', []) . "\\{$model}Controller;";
                array_splice($newLines, -1, 0, [$controllerImport]);
                $importsSection = false;
            }
        }
        
        // Process route content
        $routeLines = explode("\n", $routeContent);
        
        // Filter out the import line from route content since we added it at the top
        $filteredRouteLines = array_filter($routeLines, function($line) {
            return !str_starts_with(trim($line), 'use ') && !empty(trim($line));
        });
        
        // Add the route at the end
        $newLines[] = "";
        $newLines = array_merge($newLines, $filteredRouteLines);
        
        return implode("\n", $newLines);
    }

    /**
     * Get file path for generated class
     */
    public function getFilePath(string $model, string $type, array $options = []): string
    {
        $version = $options['api-version'] ?? $this->config('default_api_version', 'V1');
        
        return match ($type) {
            'Controller' => $this->appPath(
                $this->config('paths.controllers.api') . "/{$version}/{$model}/{$model}Controller.php"
            ),
            'StoreRequest' => $this->appPath(
                $this->config('paths.requests.api') . "/{$version}/{$model}/Store{$model}Request.php"
            ),
            'UpdateRequest' => $this->appPath(
                $this->config('paths.requests.api') . "/{$version}/{$model}/Update{$model}Request.php"
            ),
            'Resource' => $this->appPath(
                $this->config('paths.resources') . "/{$version}/{$model}/{$model}Resource.php"
            ),
            'Collection' => $this->appPath(
                $this->config('paths.resources') . "/{$version}/{$model}/{$model}Collection.php"
            ),
            default => throw new \InvalidArgumentException("Unknown API file type: {$type}")
        };
    }

    /**
     * Get namespace for generated class
     */
    public function getNamespace(string $model, string $type, array $options = []): string
    {
        $version = $options['api-version'] ?? $this->config('default_api_version', 'V1');
        
        return match ($type) {
            'Controller' => $this->config('namespaces.controllers.api') . "\\{$version}\\{$model}",
            'StoreRequest', 'UpdateRequest' => $this->config('namespaces.requests.api') . "\\{$version}\\{$model}",
            'Resource', 'Collection' => $this->config('namespaces.resources') . "\\{$version}\\{$model}",
            default => throw new \InvalidArgumentException("Unknown API namespace type: {$type}")
        };
    }

    /**
     * Get available types
     */
    public function getAvailableTypes(): array
    {
        return ['Controller', 'StoreRequest', 'UpdateRequest', 'Resource', 'Collection', 'Routes'];
    }
}