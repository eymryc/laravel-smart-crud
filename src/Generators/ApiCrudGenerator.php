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
     * Generate API Controller
     */
    public function generateController(string $model, array $options = []): bool
    {
        $version = $options['version'] ?? $this->config('default_api_version', 'V1');
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
     * Generate API requests
     */
    public function generateRequests(string $model, array $options = []): bool
    {
        $storeResult = $this->generateStoreRequest($model, $options);
        $updateResult = $this->generateUpdateRequest($model, $options);
        
        return $storeResult && $updateResult;
    }

    /**
     * Generate API routes
     */
    public function generateRoutes(string $model, array $options = []): bool
    {
        $version = $options['version'] ?? $this->config('default_api_version', 'V1');
        $filePath = $this->basePath($this->config('paths.routes.api') . "/{$version}/" . Str::kebab($model) . '.php');

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'controller' => "{$model}Controller",
            'controllerNamespace' => $this->getNamespace($model, 'Controller', $options),
            'version' => $version,
            'versionLower' => strtolower($version),
        ]);

        return $this->generateFromStub(
            $this->config('stubs.api.routes'),
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
     * Get file path for generated class
     */
    public function getFilePath(string $model, string $type, array $options = []): string
    {
        $version = $options['version'] ?? $this->config('default_api_version', 'V1');
        
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
        $version = $options['version'] ?? $this->config('default_api_version', 'V1');
        
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
