<?php

namespace Rouangni\SmartCrud\Generators;

use Illuminate\Support\Str;
use Rouangni\SmartCrud\Contracts\CrudGeneratorInterface;

class WebCrudGenerator extends AbstractGenerator implements CrudGeneratorInterface
{
    /**
     * Generate Web CRUD file
     */
    public function generate(string $model, string $type, array $options = []): bool
    {
        $method = 'generate' . $type;
        
        if (method_exists($this, $method)) {
            return $this->$method($model, $options);
        }
        
        throw new \InvalidArgumentException("Unknown Web generator type: {$type}");
    }

    /**
     * Generate all Web CRUD files
     */
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

    /**
     * Generate Web Controller (interface implementation)
     */
    public function generateController(string $model, array $options = []): bool
    {
        return $this->generateControllerFile($model, $options);
    }

    /**
     * Generate Web requests
     */
    public function generateRequests(string $model, array $options = []): bool
    {
        $storeResult = $this->generateStoreRequest($model, $options);
        $updateResult = $this->generateUpdateRequest($model, $options);
        
        return $storeResult && $updateResult;
    }

    /**
     * Generate Web routes (interface implementation)
     */
    public function generateRoutes(string $model, array $options = []): bool
    {
        return $this->generateRoutesFile($model, $options);
    }

    /**
     * Generate Web Controller
     */
    protected function generateControllerFile(string $model, array $options): bool
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

    /**
     * Generate Web Store Request
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
            $this->config('stubs.web.store_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Web Update Request
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
            $this->config('stubs.web.update_request'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Web Views
     */
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

    /**
     * Generate individual view
     */
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

    /**
     * Generate Web Routes
     */
    protected function generateRoutesFile(string $model, array $options): bool
    {
        $webRoutesFile = $this->basePath('routes/web.php');

        // Create routes/web.php if it doesn't exist
        if (!$this->files->exists($webRoutesFile)) {
            $this->createWebRoutesFile($webRoutesFile);
        }

        $routeContent = $this->generateWebRouteContent($model, $options);
        
        return $this->appendToRoutesFile($webRoutesFile, $routeContent, $model, 'Web');
    }

    /**
     * Create initial Web routes file
     */
    protected function createWebRoutesFile(string $filePath): void
    {
        $content = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\n";
        $content .= "Route::get('/', function () {\n";
        $content .= "    return view('welcome');\n";
        $content .= "});\n\n";
        $content .= "// ===== Smart CRUD Generated Routes =====\n";
        
        $this->files->put($filePath, $content);
    }

    /**
     * Generate Web route content for a model
     */
    protected function generateWebRouteContent(string $model, array $options): string
    {
        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'controller' => "{$model}Controller",
            'controllerNamespace' => $this->getNamespace($model, 'Controller', $options),
            'routePrefix' => $this->config('web.route_prefix', ''),
            'middleware' => $this->formatMiddleware($this->config('web.middleware', ['web'])),
        ]);

        $stubContent = $this->files->get($this->getStubPath($this->config('stubs.web.routes')));
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
        $routeContentAdded = false;
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
        
        // Add the route at the end
        $routeLines = explode("\n", $routeContent);
        
        // Filter out the import line from route content since we added it at the top
        $filteredRouteLines = array_filter($routeLines, function($line) {
            return !str_starts_with(trim($line), 'use ') && !empty(trim($line));
        });
        
        $newLines[] = "";
        $newLines = array_merge($newLines, $filteredRouteLines);
        
        return implode("\n", $newLines);
    }

    /**
     * Format middleware array for route file
     */
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

    /**
     * Get file path for generated class
     */
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

    /**
     * Get namespace for generated class
     */
    public function getNamespace(string $model, string $type, array $options = []): string
    {
        return match ($type) {
            'Controller' => $this->config('namespaces.controllers.web') . "\\{$model}",
            'StoreRequest', 'UpdateRequest' => $this->config('namespaces.requests.web') . "\\{$model}",
            default => throw new \InvalidArgumentException("Unknown Web namespace type: {$type}")
        };
    }

    /**
     * Get available types
     */
    public function getAvailableTypes(): array
    {
        return ['Controller', 'StoreRequest', 'UpdateRequest', 'Views', 'Routes'];
    }
}