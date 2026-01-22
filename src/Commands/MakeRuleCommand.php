<?php

namespace Otatechie\Spotlight\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRuleCommand extends Command
{
    public $signature = 'spotlight:make-rule
                        {name : The name of the rule class}
                        {--category= : Category (performance, security, architecture)}
                        {--type=advisory : Rule type (objective or advisory)}
                        {--severity=info : Severity level (info, warning, critical)}';

    public $description = 'Create a new Spotlight rule class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $category = $this->option('category') ?: $this->ask('Category', 'performance');
        $type = $this->option('type') ?: 'advisory';
        $severity = $this->option('severity') ?: 'info';

        // Ensure name ends with 'Rule'
        if (! str_ends_with($name, 'Rule')) {
            $name .= 'Rule';
        }

        $className = Str::studly($name);
        $categoryNamespace = Str::studly($category);
        $ruleId = strtolower($category).'.'.Str::kebab(Str::replaceLast('Rule', '', $className));

        // Determine namespace based on category
        $namespace = "App\\Spotlight\\Rules\\{$categoryNamespace}";
        $directory = app_path("Spotlight/Rules/{$categoryNamespace}");
        $filePath = "{$directory}/{$className}.php";

        // Create directory if it doesn't exist
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Check if file already exists
        if (File::exists($filePath)) {
            $this->error("Rule {$className} already exists at {$filePath}");

            return self::FAILURE;
        }

        // Generate rule content
        $stub = $this->getStub();
        $content = str_replace(
            [
                '{{namespace}}',
                '{{category}}',
                '{{className}}',
                '{{ruleId}}',
                '{{type}}',
                '{{severity}}',
                '{{name}}',
                '{{description}}',
            ],
            [
                $namespace,
                $category,
                $className,
                $ruleId,
                $type,
                $severity,
                Str::title(Str::replaceLast('Rule', '', $className)),
                "Checks for {$category} issues",
            ],
            $stub
        );

        File::put($filePath, $content);

        $this->info('✅ Rule created successfully!');
        $this->newLine();
        $this->line("File: {$filePath}");
        $this->line("Namespace: {$namespace}");
        $this->line("Rule ID: {$ruleId}");
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('1. Edit the rule to implement your scanning logic');
        $this->line('2. Register it in config/spotlight.php:');
        $this->line("   'custom_rules' => [");
        $this->line("       \\{$namespace}\\{$className}::class,");
        $this->line('   ],');
        $this->line('3. Run: php artisan spotlight:scan');

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return <<<'STUB'
<?php

namespace {{namespace}};

use Otatechie\Spotlight\Rules\AbstractRule;

class {{className}} extends AbstractRule
{
    protected string $type = '{{type}}';
    protected string $severity = '{{severity}}';
    protected ?string $name = '{{name}}';
    protected string $description = '{{description}}';

    public function scan(): array
    {
        // TODO: Implement your scanning logic here
        
        // Example: Check for an issue
        // if ($issueFound) {
        //     return $this->suggest(
        //         'Issue description',
        //         [
        //             'recommendation' => 'How to fix it',
        //         ]
        //     );
        // }

        return $this->pass('Check completed successfully');
    }
}
STUB;
    }
}
