<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class OverrideViewMethodCommand extends Command
{
    protected $signature = 'override:view';

    protected $description = 'Override the view method in helpers.php to support themes';

    public function handle()
    {
        $filePath = base_path('vendor/laravel/framework/src/Illuminate/Foundation/helpers.php');

        $this->info("Checking file: {$filePath}");
        // Check if file exists
        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::SUCCESS;
        }

        $content = File::get($filePath);

        if (str_contains($content, 'THEME_VIEW_OVERRIDE')) {
            $this->info('View method already overridden. Skipping.');
            return Command::SUCCESS;
        }

        $originalPattern = '#return\s+\$factory->make\(\s*\$view\s*,\s*\$data\s*,\s*\$mergeData\s*\);#';

        $this->info("File found. Modifying the view method...");

        $newContent = preg_replace(
            $originalPattern,
            "// THEME_VIEW_OVERRIDE\n\t\treturn strpos(\$view, 'frontend') !== false
                ? \$factory->make(config('app.theme') . \$view, \$data, \$mergeData)
                : \$factory->make(\$view, \$data, \$mergeData);",
            $content
        );

        if ($newContent === $content) {
            $this->error("Pattern not found or already modified.");
            return Command::SUCCESS;
        }

        File::copy($filePath, $filePath . '.bak');

        File::put($filePath, $newContent);
        $this->info("Original function overridden in helpers.php");
        return Command::SUCCESS;
    }
}
