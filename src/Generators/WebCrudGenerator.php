<?php

namespace Rouangni\SmartCrud\Generators;

use Illuminate\Support\Str;
use Rouangni\SmartCrud\Contracts\CrudGeneratorInterface;

class WebCrudGenerator extends AbstractGenerator implements CrudGeneratorInterface
{
    public function generate(string $model, string $type, array $options = []): bool
    {
        $method = 'generate' . $type;
        
        if (method_exists($this, $method)) {
            return $this->$method($model, $options);
        }
        
        throw new \InvalidArgumentException("Unknown Web generator type: {$type}");
    }

    public function generateAll(string $model, array $options = []): array
    {
        $results = [];
        $types = ['Controller', 'StoreRequest', 'UpdateRequest'];
        
        if (!($options['skip_views'] ?? false)) {
            $types[] = 'Views';
        }
        
        if (!($options['skip_routes'] ?? false)) {
            $types[] = 'Routes';
        }

        foreach ($types as $type) {
            $results[$type] = $this->generate($model, $type, $options);
        }

        return $results;
    }

    public function generateController(string $model, array $options = []): bool
    {
        $namespace = $this->getNamespace($model, 'Controller', $options);
        $className = "{$model}Controller";
        $filePath = $this->getFilePath($model, 'Controller', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
            'layout' => $this->config('web.layout', 'layouts.app'),
        ]);

        return $this->generateFromStub(
            $this->config('stubs.web.controller'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    public function generateRequests(string $model, array $options = []): bool
    {
        $storeResult = $this->generateStoreRequest($model, $options);
        $updateResult = $this->generateUpdateRequest($model, $options);
        
        return $storeResult && $updateResult;
    }

    public function generateRoutes(string $model, array $options = []): bool
    {
        $filePath = $this->basePath($this->config('paths.routes.web') . '/' . Str::kebab($model) . '.php');

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'controller' => "{$model}Controller",
            'controllerNamespace' => $this->getNamespace($model, 'Controller', $options),
            'routePrefix' => $this->config('web.route_prefix', ''),
            'middleware' => $this->formatMiddleware($this->config('web.middleware', ['web'])),
        ]);

        return $this->generateFromStub(
            $this->config('stubs.web.routes'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

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
            $this->config('stubs.web.store_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

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
            $this->config('stubs.web.update_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    protected function generateViews(string $model, array $options): bool
    {
        $views = ['index', 'create', 'edit', 'show'];
        $allSuccess = true;

        foreach ($views as $view) {
            $success = $this->generateView($model, $view, $options);
            if (!$success) {
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    protected function generateView(string $model, string $viewType, array $options): bool
    {
        $modelPluralKebab = Str::kebab(Str::plural($model));
        $filePath = $this->resourcePath("views/{$modelPluralKebab}/{$viewType}.blade.php");

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'layout' => $this->config('web.layout', 'layouts.app'),
            'viewType' => $viewType,
            'route' => $modelPluralKebab,
        ]);

        $stubPath = $this->config("stubs.web.view_{$viewType}");
        
        return $this->generateFromStub(
            $stubPath,
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    protected function formatMiddleware(array $middleware): string
    {
        if (empty($middleware)) {
            return '';
        }

        if (count($middleware) === 1) {
            return "->middleware('{$middleware[0]}')";
        }

        $formatted = "['" . implode("', '", $middleware) . "']";
        return "->middleware({$formatted})";
    }

    public function getFilePath(string $model, string $type, array $options = []): string
    {
        return match ($type) {
            'Controller' => $this->appPath(
                $this->config('paths.controllers.web') . "/{$model}/{$model}Controller.php"
            ),
            'StoreRequest' => $this->appPath(
                $this->config('paths.requests.web') . "/{$model}/Store{$model}Request.php"
            ),
            'UpdateRequest' => $this->appPath(
                $this->config('paths.requests.web') . "/{$model}/Update{$model}Request.php"
            ),
            default => throw new \InvalidArgumentException("Unknown Web file type: {$type}")
        };
    }

    public function getNamespace(string $model, string $type, array $options = []): string
    {
        return match ($type) {
            'Controller' => $this->config('namespaces.controllers.web') . "\\{$model}",
            'StoreRequest', 'UpdateRequest' => $this->config('namespaces.requests.web') . "\\{$model}",
            default => throw new \InvalidArgumentException("Unknown Web namespace type: {$type}")
        };
    }

    public function getAvailableTypes(): array
    {
        return ['Controller', 'StoreRequest', 'UpdateRequest', 'Views', 'Routes'];
    }
}
