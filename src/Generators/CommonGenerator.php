<?php

namespace Rouangni\SmartCrud\Generators;

use Illuminate\Support\Str;
use Rouangni\SmartCrud\Contracts\GeneratorInterface;

class CommonGenerator extends AbstractGenerator implements GeneratorInterface
{
    /**
     * Generate common file
     */
    public function generate(string $model, string $type, array $options = []): bool
    {
        $method = 'generate' . $type;
        
        if (method_exists($this, $method)) {
            return $this->$method($model, $options);
        }
        
        throw new \InvalidArgumentException("Unknown Common generator type: {$type}");
    }

    /**
     * Generate all common files
     */
    public function generateAll(string $model, array $options = []): array
    {
        $results = [];
        $types = $this->getAvailableTypes();

        foreach ($types as $type) {
            $results[$type] = $this->generate($model, $type, $options);
        }

        return $results;
    }

    /**
     * Generate Service
     */
    protected function generateService(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'Service', $options);
        $className = "{$model}Service";
        $filePath = $this->getFilePath($model, 'Service', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
            'repositoryInterface' => "{$model}RepositoryInterface",
            'repositoryVariable' => Str::camel($model) . 'Repository',
            'createDTO' => "{$model}CreateDTO",
            'updateDTO' => "{$model}UpdateDTO",
            'filterDTO' => "{$model}FilterDTO",
            'exception' => "{$model}Exception",
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.service'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Repository
     */
    protected function generateRepository(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'Repository', $options);
        $className = "{$model}Repository";
        $filePath = $this->getFilePath($model, 'Repository', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
            'interface' => "{$model}RepositoryInterface",
            'interfaceNamespace' => $this->getNamespace($model, 'RepositoryInterface', $options),
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.repository'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Repository Interface
     */
    protected function generateRepositoryInterface(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'RepositoryInterface', $options);
        $className = "{$model}RepositoryInterface";
        $filePath = $this->getFilePath($model, 'RepositoryInterface', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.repository_interface'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Create DTO
     */
    protected function generateCreateDTO(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'CreateDTO', $options);
        $className = "{$model}CreateDTO";
        $filePath = $this->getFilePath($model, 'CreateDTO', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.create_dto'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Update DTO
     */
    protected function generateUpdateDTO(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'UpdateDTO', $options);
        $className = "{$model}UpdateDTO";
        $filePath = $this->getFilePath($model, 'UpdateDTO', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.update_dto'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Filter DTO
     */
    protected function generateFilterDTO(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'FilterDTO', $options);
        $className = "{$model}FilterDTO";
        $filePath = $this->getFilePath($model, 'FilterDTO', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.filter_dto'),
            $filePath,
            $replacements,
            $options['force'] ?? false
        );
    }

    /**
     * Generate Exception
     */
    protected function generateException(string $model, array $options): bool
    {
        $namespace = $this->getNamespace($model, 'Exception', $options);
        $className = "{$model}Exception";
        $filePath = $this->getFilePath($model, 'Exception', $options);

        $replacements = array_merge($this->getCommonReplacements($model, $options), [
            'namespace' => $namespace,
            'class' => $className,
        ]);

        return $this->generateFromStub(
            $this->config('stubs.common.exception'),
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
        return match ($type) {
            'Service' => $this->appPath(
                $this->config('paths.services') . "/{$model}/{$model}Service.php"
            ),
            'Repository' => $this->appPath(
                $this->config('paths.repositories') . "/{$model}/{$model}Repository.php"
            ),
            'RepositoryInterface' => $this->appPath(
                $this->config('paths.repositories') . "/{$model}/Contracts/{$model}RepositoryInterface.php"
            ),
            'CreateDTO' => $this->appPath(
                $this->config('paths.dtos') . "/{$model}/{$model}CreateDTO.php"
            ),
            'UpdateDTO' => $this->appPath(
                $this->config('paths.dtos') . "/{$model}/{$model}UpdateDTO.php"
            ),
            'FilterDTO' => $this->appPath(
                $this->config('paths.dtos') . "/{$model}/{$model}FilterDTO.php"
            ),
            'Exception' => $this->appPath(
                $this->config('paths.exceptions') . "/{$model}/{$model}Exception.php"
            ),
            default => throw new \InvalidArgumentException("Unknown Common file type: {$type}")
        };
    }

    /**
     * Get namespace for generated class
     */
    public function getNamespace(string $model, string $type, array $options = []): string
    {
        return match ($type) {
            'Service' => $this->config('namespaces.services') . "\\{$model}",
            'Repository' => $this->config('namespaces.repositories') . "\\{$model}",
            'RepositoryInterface' => $this->config('namespaces.repositories') . "\\{$model}\\Contracts",
            'CreateDTO', 'UpdateDTO', 'FilterDTO' => $this->config('namespaces.dtos') . "\\{$model}",
            'Exception' => $this->config('namespaces.exceptions') . "\\{$model}",
            default => throw new \InvalidArgumentException("Unknown Common namespace type: {$type}")
        };
    }

    /**
     * Get available types
     */
    public function getAvailableTypes(): array
    {
        return [
            'Service', 
            'Repository', 
            'RepositoryInterface', 
            'CreateDTO', 
            'UpdateDTO', 
            'FilterDTO', 
            'Exception'
        ];
    }
}