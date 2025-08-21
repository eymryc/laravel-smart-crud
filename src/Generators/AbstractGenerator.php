<?php

namespace Rouangni\SmartCrud\Generators;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Rouangni\SmartCrud\Contracts\GeneratorInterface;
use Rouangni\SmartCrud\Exceptions\SmartCrudException;

abstract class AbstractGenerator implements GeneratorInterface
{
   protected Filesystem $files;
   protected ?Command $command;
   protected array $config;

   public function __construct(?Filesystem $files = null, ?Command $command = null)
   {
      $this->files = $files ?: new Filesystem();
      $this->command = $command;
      $this->config = config('smart-crud', []);
   }

   /**
    * Generate file from stub
    */
   protected function generateFromStub(
      string $stubPath,
      string $filePath,
      array $replacements,
      bool $force = false
   ): bool {
      if (!$force && $this->exists($filePath)) {
         $this->info("File already exists: {$filePath}");
         return false;
      }

      // Create directory if it doesn't exist
      $directory = dirname($filePath);
      if (!$this->files->isDirectory($directory)) {
         $this->files->makeDirectory($directory, 0755, true);
      }

      // Get stub content
      $stubFullPath = $this->getStubPath($stubPath);

      if (!$this->files->exists($stubFullPath)) {
         throw new SmartCrudException("Stub not found: {$stubPath}");
      }

      $content = $this->files->get($stubFullPath);

      // Replace placeholders
      $content = $this->replacePlaceholders($content, $replacements);

      // Write file
      $this->files->put($filePath, $content);
      $this->info("Generated: {$filePath}");

      return true;
   }

   /**
    * Check if a file already exists
    */
   public function exists(string $filePath): bool
   {
      return $this->files->exists($filePath);
   }

   /**
    * Replace placeholders in content
    */
   // protected function replacePlaceholders(string $content, array $replacements): string
   // {
   //    foreach ($replacements as $key => $value) {
   //       $content = str_replace([
   //          "{{ {$key} }}",
   //          "{{${key}}}",
   //          "{{ ${key} }}",
   //          "{{{$key}}}",
   //       ], $value, $content);
   //    }

   //    return $content;
   // }
   protected function replacePlaceholders(string $content, array $replacements): string
   {
      foreach ($replacements as $key => $value) {
         $content = str_replace([
            "{{ {$key} }}",
            "{{{$key}}}",
         ], $value, $content);
      }

      return $content;
   }


   /**
    * Get full path to stub file
    */
   protected function getStubPath(string $stubPath): string
   {
      // Check if custom stubs exist in resources
      $customStubPath = resource_path("stubs/smart-crud/{$stubPath}");
      if ($this->files->exists($customStubPath)) {
         return $customStubPath;
      }

      // Use package stubs
      return __DIR__ . "/../Stubs/{$stubPath}";
   }

   /**
    * Get common replacements for stubs
    */
   protected function getCommonReplacements(string $model, array $options = []): array
   {
      return [
         'model' => $model,
         'modelVariable' => Str::camel($model),
         'modelPlural' => Str::plural($model),
         'modelPluralVariable' => Str::camel(Str::plural($model)),
         'modelKebab' => Str::kebab($model),
         'modelPluralKebab' => Str::kebab(Str::plural($model)),
         'modelSnake' => Str::snake($model),
         'modelPluralSnake' => Str::snake(Str::plural($model)),
         'modelTitle' => Str::title($model),
         'modelPluralTitle' => Str::title(Str::plural($model)),
         'modelLower' => Str::lower($model),
         'modelPluralLower' => Str::lower(Str::plural($model)),
      ];
   }

   /**
    * Log info message
    */
   protected function info(string $message): void
   {
      if ($this->command) {
         $this->command->info($message);
      }
   }

   /**
    * Log error message
    */
   protected function error(string $message): void
   {
      if ($this->command) {
         $this->command->error($message);
      }
   }

   /**
    * Log warning message
    */
   protected function warn(string $message): void
   {
      if ($this->command) {
         $this->command->warn($message);
      }
   }

   /**
    * Get configuration value
    */
   protected function config(string $key, $default = null)
   {
      return data_get($this->config, $key, $default);
   }

   /**
    * Get app path with given path
    */
   protected function appPath(string $path = ''): string
   {
      return app_path($path);
   }

   /**
    * Get base path with given path
    */
   protected function basePath(string $path = ''): string
   {
      return base_path($path);
   }

   /**
    * Get resource path with given path
    */
   protected function resourcePath(string $path = ''): string
   {
      return resource_path($path);
   }
}
