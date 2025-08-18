<?php

namespace Rouangni\SmartCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Rouangni\SmartCrud\SmartCrudGenerator;

class SmartCrudCommand extends Command
{
    protected $signature = 'make:smart-crud 
                            {name : The name of the resource}
                            {--force : Overwrite existing files}
                            {--no-migration : Do not create migration}
                            {--no-factory : Do not create factory}
                            {--no-seeder : Do not create seeder}
                            {--no-routes : Do not register routes}';

    protected $description = 'Generate intelligent CRUD based on database structure';

    private SmartCrudGenerator $generator;

    public function __construct(SmartCrudGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $options = $this->getCrudOptions();

        $this->info("🚀 Generating Smart CRUD for: {$name}");
        $this->newLine();

        try {
            // Generate basic Laravel files first if needed
            if (!$options['no-migration'] || !$options['no-factory'] || !$options['no-seeder']) {
                $this->generateBasicFiles($name, $options);
            }

            // Run migration if needed
            if (!$options['no-migration']) {
                $this->runMigration();
            }

            // Generate smart CRUD files
            $this->info("📝 Generating smart CRUD files...");
            $generatedFiles = $this->generator->generate($name, $options);

            // Display success message
            $this->displaySuccess($name, $generatedFiles);

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function getCrudOptions(): array
    {
        return [
            'force' => $this->option('force'),
            'no-migration' => $this->option('no-migration'),
            'no-factory' => $this->option('no-factory'),
            'no-seeder' => $this->option('no-seeder'),
            'no-routes' => $this->option('no-routes'),
        ];
    }

    private function generateBasicFiles(string $name, array $options): void
    {
        $this->info("📁 Generating basic Laravel files...");

        $modelOptions = ['name' => $name];
        
        if (!$options['no-migration']) {
            $modelOptions['--migration'] = true;
        }
        
        if (!$options['no-factory']) {
            $modelOptions['--factory'] = true;
        }
        
        if (!$options['no-seeder']) {
            $modelOptions['--seed'] = true;
        }

        $this->call('make:model', $modelOptions);
    }

    private function runMigration(): void
    {
        if ($this->confirm('Run migration now? (Recommended)', true)) {
            $this->info("🔄 Running migration...");
            $this->call('migrate');
        } else {
            $this->warn("⚠️  Please run migration manually before using the generated CRUD");
        }
    }

    private function displaySuccess(string $name, array $generatedFiles): void
    {
        $this->newLine();
        $this->info("✅ Smart CRUD generated successfully for: {$name}");
        $this->newLine();

        $this->comment("📁 Generated files:");
        
        if (isset($generatedFiles['controller'])) {
            $this->line("   • Controller: " . $this->getRelativePath($generatedFiles['controller']));
        }
        
        if (isset($generatedFiles['service'])) {
            $this->line("   • Service: " . $this->getRelativePath($generatedFiles['service']));
        }
        
        if (isset($generatedFiles['repository'])) {
            $this->line("   • Repository: " . $this->getRelativePath($generatedFiles['repository']));
        }
        
        if (isset($generatedFiles['repository_interface'])) {
            $this->line("   • Repository Interface: " . $this->getRelativePath($generatedFiles['repository_interface']));
        }
        
        if (isset($generatedFiles['dtos'])) {
            foreach ($generatedFiles['dtos'] as $type => $path) {
                $this->line("   • DTO ({$type}): " . $this->getRelativePath($path));
            }
        }
        
        if (isset($generatedFiles['requests'])) {
            foreach ($generatedFiles['requests'] as $type => $path) {
                $this->line("   • Request ({$type}): " . $this->getRelativePath($path));
            }
        }
        
        if (isset($generatedFiles['resources'])) {
            foreach ($generatedFiles['resources'] as $type => $path) {
                $this->line("   • Resource ({$type}): " . $this->getRelativePath($path));
            }
        }
        
        if (isset($generatedFiles['exception'])) {
            $this->line("   • Exception: " . $this->getRelativePath($generatedFiles['exception']));
        }

        $this->newLine();
        $this->comment("🔧 Next steps:");
        
        if (!$this->option('no-migration')) {
            $this->line("1. ✅ Migration already run");
        } else {
            $this->line("1. Run: php artisan migrate");
        }
        
        $this->line("2. Register repository binding in a Service Provider:");
        $this->line("   \$this->app->bind(");
        $this->line("       \\App\\Repositories\\Contracts\\{$name}RepositoryInterface::class,");
        $this->line("       \\App\\Repositories\\{$name}Repository::class");
        $this->line("   );");
        
        $this->newLine();
        $this->comment("🚀 API Endpoints:");
        $tableName = Str::snake(Str::plural($name));
        $this->line("   GET    /api/{$tableName}      - List with filters");
        $this->line("   POST   /api/{$tableName}      - Create new");
        $this->line("   GET    /api/{$tableName}/{id} - Show specific");
        $this->line("   PUT    /api/{$tableName}/{id} - Update");
        $this->line("   DELETE /api/{$tableName}/{id} - Delete");
        
        $this->newLine();
        $this->comment("📖 Usage examples:");
        $this->line("   # List with search");
        $this->line("   GET /api/{$tableName}?search=keyword&sort_by=created_at&sort_direction=desc");
        $this->line("   ");
        $this->line("   # Create new");
        $this->line("   POST /api/{$tableName} + JSON body");
        
        $this->newLine();
        $this->comment("📋 Response format:");
        $this->line('   {');
        $this->line('     "success": true,');
        $this->line('     "message": "Operation successful",');
        $this->line('     "data": {...},');
        $this->line('     "status": 200');
        $this->line('   }');

        $this->newLine();
        $this->info("🎉 Your CRUD is ready to use!");
    }

    private function getRelativePath(string $path): string
    {
        return str_replace(base_path() . '/', '', $path);
    }
}