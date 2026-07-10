<?php

namespace App\Console\Commands;

use App\Jobs\MinimizeOrderJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class SyncStagingDatabase extends Command
{
    protected $signature = 'db:sync-staging';
    protected $description = 'Dump production database and restore into staging database';

    public function handle()
    {
        Log::channel('custom')->info('Starting database sync at ' . now());
        $this->info('Starting database sync...');

        $production = [
            'host' => env('DB_HOST'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
        ];

        $staging = [
            'host' => env('STAGING_DB_HOST'),
            'database' => env('STAGING_DB_DATABASE'),
            'username' => env('STAGING_DB_USERNAME'),
            'password' => env('STAGING_DB_PASSWORD'),
        ];

        // If all staging credentials are empty, exit gracefully
        if (empty(array_filter($staging)) || count(array_filter($staging)) < 3) {
            $this->warn('⚠️  Staging database credentials are missing in .env');
            return Command::FAILURE; // gracefully exit Artisan command
        }

        $timestamp = now()->format('Y_m_d_H_i_s');
        $dumpDir = storage_path("app/db_sync/{$timestamp}");
        $backupFile = "{$dumpDir}/production_dump.sql";

        if (!is_dir($dumpDir)) {
            mkdir($dumpDir, 0775, true);
        }

        // Create temporary MySQL config files
        $prodConfigFile = $this->createMysqlConfig($production, $dumpDir, 'prod');
        $stagingConfigFile = $this->createMysqlConfig($staging, $dumpDir, 'staging');

        try {
            // Step 1: Dump the production database
            $dumpCommand = sprintf(
                'mysqldump --defaults-extra-file=%s -h%s %s > %s',
                $this->escapeShellArg($prodConfigFile),
                $this->escapeShellArg($production['host']),
                $this->escapeShellArg($production['database']),
                $this->escapeShellArg($backupFile)
            );

            /**
             * If you prefer to dump database without business_settings table
             */
            // $dumpCommand = sprintf(
            //     'mysqldump --defaults-extra-file=%s -h%s %s --ignore-table=%s > %s',
            //     $this->escapeShellArg($prodConfigFile),
            //     $this->escapeShellArg($production['host']),
            //     $this->escapeShellArg($production['database']),
            //     $this->escapeShellArg($production['database'] . '.business_settings'),
            //     $this->escapeShellArg($backupFile)
            // );

            $this->info('Dumping production database...');
            $this->runProcess($dumpCommand, 'Dump Error');

            // Step 2: Empty the staging database
            $this->info('🧹 Resetting staging database...');

            $this->dropStagingDatabase($staging['database']); // Drop and recreate the staging database
            //$this->dropTablesInStaging($staging['database']); // If you prefer to keep business_settings

            // Step 3: Import the dump file into staging database
            $importCommand = sprintf(
                'mysql --defaults-extra-file=%s -h%s %s < %s',
                $this->escapeShellArg($stagingConfigFile),
                $this->escapeShellArg($staging['host']),
                $this->escapeShellArg($staging['database']),
                $this->escapeShellArg($backupFile)
            );

            $this->info('Importing into staging database...');
            $this->runProcess($importCommand, 'Import Error');

            // Step 4: Dispatch jobs to minimize orders
            MinimizeOrderJob::dispatch();

            // Step 5: Clear Cache In Staging
            if (!empty(env('STAGING_APP_URL'))) {
                try{
                    $this->info('Clearing cache in staging database...');
                    $response = Http::get(env('STAGING_APP_URL') . '/api/forge-cache');
                    if ($response->successful() && $response->json('success')) {
                        $this->info('Cache cleared successfully in staging.');
                    } else {
                        $this->warn('Failed to clear cache in staging.');
                    }
                } catch (\Exception $e) {
                    $this->warn('Failed to clear cache in staging: ' . $e->getMessage());
                }
            }

            $this->info('✅ Database synced successfully!');
            Log::channel('custom')->info('✅ Database sync completed successfully on ' . now());
        } catch (\Exception $e) {
            $this->error('❌ Database sync failed. Check logs for details.');
            Log::channel('custom')->error('❌ Database sync failed on ' . now(), ['error' => $e->getMessage()]);
            return Command::FAILURE;
        } finally {
            // Clean up config files
            if (file_exists($prodConfigFile)) {
                unlink($prodConfigFile);
            }
            if (file_exists($stagingConfigFile)) {
                unlink($stagingConfigFile);
            }
        }
    }

    protected function dropStagingDatabase(string $database)
    {
        DB::connection('staging')->statement("DROP DATABASE IF EXISTS `{$database}`");
        DB::connection('staging')->statement("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    protected function dropTablesInStaging(string $database)
    {
        $conn = DB::connection('staging');

        $this->warn("Dropping all tables from '{$database}' except 'business_settings'...");

        $tables = $conn->select("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = ?
            AND table_name != 'business_settings'
        ", [$database]);

        if (empty($tables)) {
            $this->info("No tables to drop. Database is already clean.");
            return;
        }

        $tableNames = collect($tables)->pluck('table_name')->toArray();

        $this->info("Dropping " . count($tableNames) . " tables.");

        $conn->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tableNames as $table) {
            try {
                $conn->statement("DROP TABLE IF EXISTS `$table`");
            } catch (\Exception $ex) {
                $this->error("❌ Failed to drop {$table}: " . $ex->getMessage());
            }
        }

        $conn->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info("🎯 Completed. All tables dropped except 'business_settings'.");
    }

    /**
     * Create a temporary MySQL configuration file (cross-platform)
     */
    protected function createMysqlConfig(array $config, string $dir, string $prefix): string
    {
        $configFile = "{$dir}/{$prefix}_mysql.cnf";

        $content = "[client]\n";
        $content .= "user={$config['username']}\n";
        $content .= "password=\"{$config['password']}\"\n";

        file_put_contents($configFile, $content);

        // Set permissions (Linux only, gracefully ignored on Windows)
        if (PHP_OS_FAMILY !== 'Windows') {
            chmod($configFile, 0600);
        }

        return $configFile;
    }

    /**
     * Cross-platform shell argument escaping
     */
    protected function escapeShellArg(string $arg): string
    {
        // On Windows, paths with backslashes need special handling
        if (PHP_OS_FAMILY === 'Windows') {
            // Convert backslashes to forward slashes for MySQL commands
            // MySQL on Windows accepts both / and \ as path separators
            $arg = str_replace('\\', '/', $arg);
        }

        return escapeshellarg($arg);
    }

    protected function runProcess(string $command, string $errorLabel)
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(0);
        $process->setIdleTimeout(0);
        $process->run(function ($type, $buffer) use ($errorLabel) {
            if ($type === Process::ERR) {
                echo "{$errorLabel}: $buffer\n";
            }
        });

        if (!$process->isSuccessful()) {
            throw new \RuntimeException("{$errorLabel}: " . $process->getErrorOutput());
        }
    }
}
