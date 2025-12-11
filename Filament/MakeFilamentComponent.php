
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeFilamentComponent extends Command
{
    protected const COMPONENT_PATH = 'Schemas/Components';

    protected $signature = 'MakeFilamentComponent {name} {--type=field} {--path=}';
    protected $description = 'Create a new Filament 4 component with view';

    public function handle()
    {
        $name = $this->argument('name');
        $customPath = $this->option('path');
        
        $componentName = Str::studly($name);
        $viewName = Str::kebab($name);

        // Xác định đường dẫn
        if ($customPath) {
            $basePath = base_path("{$customPath}/{$componentName}");
            $namespace = $this->pathToNamespace($customPath) . "\\{$componentName}";
            $viewNamespace = 'filament::' . str_replace('/', '.', trim(str_replace('App/Filament/', '', $customPath), '/'));
        } else {
            $basePath = app_path('Filament/' . self::COMPONENT_PATH . "/{$componentName}");
            $namespace = 'App\\Filament\\' . str_replace('/', '\\', self::COMPONENT_PATH) . "\\{$componentName}";
            $viewNamespace = 'filament::' . str_replace('/', '.', self::COMPONENT_PATH);
        }

        $phpFile = "{$basePath}/{$componentName}.php";
        $bladeFile = "{$basePath}/{$viewName}.blade.php";

        // Tạo thư mục
        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        // Tạo files
        File::put($phpFile, $this->getPhpStub($componentName, $viewName, $namespace, $viewNamespace));
        File::put($bladeFile, $this->getBladeStub($componentName));

        // Thông báo
        $this->components->info("Component created successfully!");
        $this->components->info("PHP: {$phpFile}");
        $this->components->info("Blade: {$bladeFile}");

        $this->newLine();
        $this->components->info("Usage example:");
        $this->line("use {$namespace}\\{$componentName};");
        $this->newLine();
        $this->line("{$componentName}::make('field_name')");
        $this->line("    ->label('My Field')");
        $this->line("    ->required()");
    }

    protected function getPhpStub($componentName, $viewName, $namespace, $viewNamespace)
    {
        $viewPath = "{$viewNamespace}.{$componentName}.{$viewName}";
        
        return <<<PHP
<?php

namespace {$namespace};

use Filament\Forms\Components\Field;

class {$componentName} extends Field
{
    protected string \$view = '{$viewPath}';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Component setup logic
    }

    public function placeholder(string | \Closure \$placeholder): static
    {
        \$this->placeholder = \$placeholder;
        return \$this;
    }

    public function prefix(string | \Closure \$prefix): static
    {
        \$this->prefix = \$prefix;
        return \$this;
    }

    public function suffix(string | \Closure \$suffix): static
    {
        \$this->suffix = \$suffix;
        return \$this;
    }
}

PHP;
    }

    protected function getBladeStub($componentName)
    {
        return <<<BLADE
<x-dynamic-component
    :component="\$getFieldWrapperView()"
    :field="\$field"
>
    <div x-data="{ state: \$wire.\$entangle('{{ \$getStatePath() }}') }">
        {{-- Your custom {$componentName} component markup here --}}
        
    </div>
</x-dynamic-component>
BLADE;
    }

    protected function pathToNamespace($path): string
    {
        return str_replace('/', '\\', $path);
    }
}



// AppServiceProvider.php
// protected function bootFilamentComponent()
// {
//     View::addNamespace('filament',app_path('Filament'));
// }
