<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseDumpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:dump
        {--filename= : Custom filename for the dump file}
        {--gzip : Compress the output file with gzip (.sql.gz)}
        {--schema-only : Export database schema only without table data}
        {--connection= : The database connection to use (defaults to default connection)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export/dump the database to a SQL file in database/dumps';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startTime = microtime(true);
        $connectionName = $this->option('connection') ?: config('database.default');
        $config = config("database.connections.{$connectionName}");

        if (! $config) {
            $this->error("Database connection [{$connectionName}] is not configured.");

            return self::FAILURE;
        }

        $driver = $config['driver'] ?? 'mysql';
        $dumpDir = database_path('dumps');

        if (! File::isDirectory($dumpDir)) {
            File::makeDirectory($dumpDir, 0755, true);
        }

        $isGzip = (bool) $this->option('gzip');
        $isSchemaOnly = (bool) $this->option('schema-only');

        $filename = $this->resolveFilename($config, $isGzip, $isSchemaOnly);
        $targetPath = $dumpDir.DIRECTORY_SEPARATOR.$filename;

        $this->info("Starting database dump for connection [{$connectionName}] ({$driver})...");
        if ($isSchemaOnly) {
            $this->comment('Mode: Schema only (no data)');
        }
        if ($isGzip) {
            $this->comment('Compression: gzip enabled');
        }

        try {
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $this->dumpMySql($config, $targetPath, $isGzip, $isSchemaOnly);
            } elseif ($driver === 'sqlite') {
                $this->dumpSqlite($config, $targetPath, $isGzip, $isSchemaOnly);
            } else {
                $this->error("Driver [{$driver}] is not currently supported for automated dumping.");

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            if (File::exists($targetPath)) {
                File::delete($targetPath);
            }
            $this->error('Failed to dump database: '.$e->getMessage());

            return self::FAILURE;
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $fileSize = $this->formatBytes(File::size($targetPath));
        $relativePath = 'database/dumps/'.$filename;

        $this->newLine();
        $this->info("✓ Database dumped successfully in {$elapsed}s!");
        $this->line("  <comment>File:</comment> {$relativePath}");
        $this->line("  <comment>Size:</comment> {$fileSize}");
        $this->line("  <comment>Path:</comment> {$targetPath}");

        return self::SUCCESS;
    }

    /**
     * Resolve the target dump filename.
     */
    protected function resolveFilename(array $config, bool $isGzip, bool $isSchemaOnly): string
    {
        $customName = $this->option('filename');

        if ($customName) {
            $name = basename($customName);
            if ($isGzip && ! str_ends_with($name, '.gz')) {
                $name .= '.gz';
            }

            return $name;
        }

        $dbName = $config['database'] ?? 'db';
        if ($config['driver'] === 'sqlite') {
            $dbName = pathinfo($dbName, PATHINFO_FILENAME) ?: 'sqlite';
        }

        $timestamp = date('Y-m-d_His');
        $prefix = $isSchemaOnly ? "schema_{$dbName}_{$timestamp}" : "dump_{$dbName}_{$timestamp}";
        $extension = $isGzip ? 'sql.gz' : 'sql';

        return "{$prefix}.{$extension}";
    }

    /**
     * Dump a MySQL / MariaDB database.
     */
    protected function dumpMySql(array $config, string $targetPath, bool $isGzip, bool $isSchemaOnly): void
    {
        $dumpBinary = $this->findBinary(['mariadb-dump', 'mysqldump']);
        if (! $dumpBinary) {
            throw new \RuntimeException("Neither 'mariadb-dump' nor 'mysqldump' binary was found in PATH.");
        }

        $args = [$dumpBinary];

        $host = $config['host'] ?? '127.0.0.1';
        $port = (string) ($config['port'] ?? 3306);
        $username = $config['username'] ?? 'root';
        $database = $config['database'] ?? '';
        $socket = $config['unix_socket'] ?? ($config['socket'] ?? null);

        if (! empty($socket)) {
            $args[] = "--socket={$socket}";
        } else {
            $args[] = "--host={$host}";
            $args[] = "--port={$port}";
        }

        if (! empty($username)) {
            $args[] = "--user={$username}";
        }

        // Standard flags for clean dump
        $args[] = '--single-transaction';
        $args[] = '--quick';
        $args[] = '--routines';
        $args[] = '--triggers';

        if ($isSchemaOnly) {
            $args[] = '--no-data';
        }

        $args[] = $database;

        $env = [];
        $password = (string) ($config['password'] ?? '');
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        $this->executeAndStream($args, $env, $targetPath, $isGzip);
    }

    /**
     * Dump a SQLite database.
     */
    protected function dumpSqlite(array $config, string $targetPath, bool $isGzip, bool $isSchemaOnly): void
    {
        $dbPath = $config['database'] ?? '';
        if (! File::exists($dbPath)) {
            throw new \RuntimeException("SQLite database file not found at: [{$dbPath}]");
        }

        $sqliteBinary = $this->findBinary(['sqlite3']);
        if (! $sqliteBinary) {
            throw new \RuntimeException("'sqlite3' binary was not found in PATH.");
        }

        $args = [$sqliteBinary, $dbPath];
        if ($isSchemaOnly) {
            $args[] = '.schema';
        } else {
            $args[] = '.dump';
        }

        $this->executeAndStream($args, [], $targetPath, $isGzip);
    }

    /**
     * Execute process and stream stdout to the target file (optionally gzip-compressed).
     */
    protected function executeAndStream(array $command, array $env, string $targetPath, bool $isGzip): void
    {
        $fileHandle = $isGzip ? gzopen($targetPath, 'wb9') : fopen($targetPath, 'wb');
        if ($fileHandle === false) {
            throw new \RuntimeException("Could not open file handle for target: [{$targetPath}]");
        }

        $process = new Process($command, base_path(), $env, null, 0);
        $processErrors = '';

        try {
            $process->run(function ($type, $buffer) use ($fileHandle, $isGzip, &$processErrors) {
                if ($type === Process::OUT) {
                    if ($isGzip) {
                        gzwrite($fileHandle, $buffer);
                    } else {
                        fwrite($fileHandle, $buffer);
                    }
                } else {
                    $processErrors .= $buffer;
                }
            });
        } finally {
            if ($isGzip) {
                gzclose($fileHandle);
            } else {
                fclose($fileHandle);
            }
        }

        if (! $process->isSuccessful()) {
            $errorMessage = trim($processErrors) ?: $process->getErrorOutput();
            throw new \RuntimeException("Dump process failed (exit code {$process->getExitCode()}): {$errorMessage}");
        }
    }

    /**
     * Find the first available binary in PATH.
     */
    protected function findBinary(array $binaries): ?string
    {
        foreach ($binaries as $binary) {
            $proc = new Process(['which', $binary]);
            $proc->run();
            if ($proc->isSuccessful()) {
                $path = trim($proc->getOutput());
                if (! empty($path) && is_executable($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    /**
     * Format bytes into human readable format.
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}
