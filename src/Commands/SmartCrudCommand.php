<?php

namespace Rouangni\SmartCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Rouangni\SmartCrud\Generators\ApiCrudGenerator;
use Rouangni\SmartCrud\Generators\WebCrudGenerator;
use Rouangni\SmartCrud\Generators\CommonGenerator;
use Rouangni\SmartCrud\Exceptions\SmartCrudException;

class SmartCrudCommand extends Command
{
   /**
    * The name and signature of the console command.
    */
   protected $signature = 'make:smart-crud 
                           {model : The model name}
                           {--api : Generate API CRUD}
                           {--web : Generate Web CRUD}
                           {--version=V1 : API version (default: V1)}
                           {--force : Overwrite existing files}
                           {--skip-common : Skip common files (Service, Repository, etc.)}
                           {--skip-routes : Skip route generation}
                           {--skip-views : Skip view generation (Web only)}
                           {--dry-run : Show what would be generated without creating files}';

   /**
    * The console command description.
    */
   protected $description = 'Generate a complete CRUD with API/Web options and organized namespacing';

   protected ApiCrudGenerator $apiGenerator;
   protected WebCrudGenerator $webGenerator;
   protected CommonGenerator $commonGenerator;

   public function __construct(
      ApiCrudGenerator $apiGenerator,
      WebCrudGenerator $webGenerator,
      CommonGenerator $commonGenerator
   ) {
      parent::__construct();

      $this->apiGenerator = $apiGenerator;
      $this->webGenerator = $webGenerator;
      $this->commonGenerator = $commonGenerator;
   }

   /**
    * Execute the console command.
    */
   public function handle(): int
   {
      try {
         $model = $this->argument('model');
         $options = $this->getOptions();

         // Validation
         if (!$options['api'] && !$options['web']) {
            $this->error('You must specify either --api or --web option (or both).');
            return self::FAILURE;
         }

         $this->displayHeader($model, $options);

         if ($options['dry_run']) {
            return $this->performDryRun($model, $options);
         }

         // Generate files
         $results = $this->generateFiles($model, $options);

         // Display results
         $this->displayResults($results);
         $this->showNextSteps($model, $options);

         return self::SUCCESS;
      } catch (SmartCrudException $e) {
         $this->error($e->getMessage());
         return self::FAILURE;
      } catch (\Exception $e) {
         $this->error('An unexpected error occurred: ' . $e->getMessage());
         return self::FAILURE;
      }
   }

   /**
    * Get processed options
    */
   protected function getOptions(): array
   {
      return [
         'api' => $this->option('api'),
         'web' => $this->option('web'),
         'version' => $this->option('version') ?: config('smart-crud.default_api_version', 'V1'),
         'force' => $this->option('force'),
         'skip_common' => $this->option('skip-common'),
         'skip_routes' => $this->option('skip-routes'),
         'skip_views' => $this->option('skip-views'),
         'dry_run' => $this->option('dry-run'),
      ];
   }

   /**
    * Display command header
    */
   protected function displayHeader(string $model, array $options): void
   {
      $this->info("🚀 Generating Smart CRUD for: <comment>{$model}</comment>");

      $types = [];
      if ($options['api']) {
         $types[] = "API ({$options['version']})";
      }
      if ($options['web']) {
         $types[] = "Web";
      }

      $this->line("📦 Types: " . implode(', ', $types));
      $this->newLine();
   }

   /**
    * Perform dry run - show what would be generated
    */
   protected function performDryRun(string $model, array $options): int
   {
      $this->warn('🔍 DRY RUN - No files will be created');
      $this->newLine();

      $files = $this->getFilesToGenerate($model, $options);

      foreach ($files as $category => $categoryFiles) {
         $this->line("<info>{$category}:</info>");
         foreach ($categoryFiles as $file) {
            $this->line("  📄 {$file}");
         }
         $this->newLine();
      }

      $this->info('💡 Run without --dry-run to generate these files');
      return self::SUCCESS;
   }

   /**
    * Get list of files that would be generated
    */
   protected function getFilesToGenerate(string $model, array $options): array
   {
      $files = [];

      // Common files
      if (!$options['skip_common']) {
         $files['Common Files'] = [
            "app/Services/{$model}/{$model}Service.php",
            "app/Repositories/{$model}/{$model}Repository.php",
            "app/Repositories/{$model}/Contracts/{$model}RepositoryInterface.php",
            "app/DTOs/{$model}/{$model}CreateDTO.php",
            "app/DTOs/{$model}/{$model}UpdateDTO.php",
            "app/DTOs/{$model}/{$model}FilterDTO.php",
            "app/Exceptions/{$model}/{$model}Exception.php",
         ];
      }

      // API files
      if ($options['api']) {
         $version = $options['version'];
         $files["API Files ({$version})"] = [
            "app/Http/Controllers/Api/{$version}/{$model}/{$model}Controller.php",
            "app/Http/Requests/Api/{$version}/{$model}/Store{$model}Request.php",
            "app/Http/Requests/Api/{$version}/{$model}/Update{$model}Request.php",
            "app/Http/Resources/Api/{$version}/{$model}/{$model}Resource.php",
            "app/Http/Resources/Api/{$version}/{$model}/{$model}Collection.php",
         ];

         if (!$options['skip_routes']) {
            $files["API Files ({$version})"][] = "routes/api/{$version}/" . Str::kebab($model) . ".php";
         }
      }

      // Web files
      if ($options['web']) {
         $files['Web Files'] = [
            "app/Http/Controllers/Web/{$model}/{$model}Controller.php",
            "app/Http/Requests/Web/{$model}/Store{$model}Request.php",
            "app/Http/Requests/Web/{$model}/Update{$model}Request.php",
         ];

         if (!$options['skip_views']) {
            $modelKebab = Str::kebab(Str::plural($model));
            $files['Web Files'] = array_merge($files['Web Files'], [
               "resources/views/{$modelKebab}/index.blade.php",
               "resources/views/{$modelKebab}/create.blade.php",
               "resources/views/{$modelKebab}/edit.blade.php",
               "resources/views/{$modelKebab}/show.blade.php",
            ]);
         }

         if (!$options['skip_routes']) {
            $files['Web Files'][] = "routes/web/" . Str::kebab($model) . ".php";
         }
      }

      return $files;
   }

   /**
    * Generate all files
    */
   protected function generateFiles(string $model, array $options): array
   {
      $results = [
         'common' => [],
         'api' => [],
         'web' => [],
      ];

      // Generate common files
      if (!$options['skip_common']) {
         $this->info('📁 Generating common files...');
         $results['common'] = $this->generateCommonFiles($model, $options);
      }

      // Generate API files
      if ($options['api']) {
         $this->info("🔌 Generating API files ({$options['version']})...");
         $results['api'] = $this->generateApiFiles($model, $options);
      }

      // Generate Web files
      if ($options['web']) {
         $this->info('🌐 Generating Web files...');
         $results['web'] = $this->generateWebFiles($model, $options);
      }

      return $results;
   }

   /**
    * Generate common files
    */
   protected function generateCommonFiles(string $model, array $options): array
   {
      $results = [];
      $types = ['Service', 'Repository', 'RepositoryInterface', 'CreateDTO', 'UpdateDTO', 'FilterDTO', 'Exception'];

      foreach ($types as $type) {
         try {
            $generated = $this->commonGenerator->generate($model, $type, $options);
            $results[$type] = $generated ? 'created' : 'skipped';
         } catch (\Exception $e) {
            $results[$type] = 'failed: ' . $e->getMessage();
            $this->error("Failed to generate {$type}: " . $e->getMessage());
         }
      }

      return $results;
   }

   /**
    * Generate API files
    */
   protected function generateApiFiles(string $model, array $options): array
   {
      $results = [];
      $types = ['Controller', 'StoreRequest', 'UpdateRequest', 'Resource', 'Collection'];

      if (!$options['skip_routes']) {
         $types[] = 'Routes';
      }

      foreach ($types as $type) {
         try {
            $generated = $this->apiGenerator->generate($model, $type, $options);
            $results[$type] = $generated ? 'created' : 'skipped';
         } catch (\Exception $e) {
            $results[$type] = 'failed: ' . $e->getMessage();
            $this->error("Failed to generate API {$type}: " . $e->getMessage());
         }
      }

      return $results;
   }

   /**
    * Generate Web files
    */
   protected function generateWebFiles(string $model, array $options): array
   {
      $results = [];
      $types = ['Controller', 'StoreRequest', 'UpdateRequest'];

      if (!$options['skip_views']) {
         $types[] = 'Views';
      }

      if (!$options['skip_routes']) {
         $types[] = 'Routes';
      }

      foreach ($types as $type) {
         try {
            $generated = $this->webGenerator->generate($model, $type, $options);
            $results[$type] = $generated ? 'created' : 'skipped';
         } catch (\Exception $e) {
            $results[$type] = 'failed: ' . $e->getMessage();
            $this->error("Failed to generate Web {$type}: " . $e->getMessage());
         }
      }

      return $results;
   }

   /**
    * Display generation results
    */
   protected function displayResults(array $results): void
   {
      $this->newLine();
      $this->info('📋 Generation Results:');

      foreach ($results as $category => $categoryResults) {
         if (empty($categoryResults)) {
            continue;
         }

         $this->line("<comment>" . ucfirst($category) . " Files:</comment>");

         foreach ($categoryResults as $type => $status) {
            $icon = match ($status) {
               'created' => '✅',
               'skipped' => '⏭️',
               default => '❌'
            };

            $this->line("  {$icon} {$type}: {$status}");
         }

         $this->newLine();
      }
   }

   /**
    * Show next steps
    */
   protected function showNextSteps(string $model, array $options): void
   {
      $this->info('🎯 Next steps:');

      $steps = [
         '1. Run migrations if needed',
         '2. Register routes in your RouteServiceProvider',
      ];

      if ($options['api']) {
         $version = strtolower($options['version']);
         $modelKebab = Str::kebab(Str::plural($model));
         $steps[] = "3. API endpoints available at: /api/{$version}/{$modelKebab}";
      }

      if ($options['web']) {
         $modelKebab = Str::kebab(Str::plural($model));
         $steps[] = "3. Web routes available at: /{$modelKebab}";
      }

      $steps = array_merge($steps, [
         '4. Configure database relationships in generated files',
         '5. Customize validation rules in Request classes',
         '6. Update Resource/Collection classes for API responses',
      ]);

      foreach ($steps as $step) {
         $this->line("  {$step}");
      }

      $this->newLine();
      $this->info('🎉 Smart CRUD generation completed successfully!');
   }
}
