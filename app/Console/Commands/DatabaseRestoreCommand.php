<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class DatabaseRestoreCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore
        {--file= : Specific dump file path or filename in database/dumps to restore}
        {--connection= : Database connection to use (defaults to default connection)}
        {--force : Force restore without interactive confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore the database from a SQL or gzipped dump file';

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
        $targetFile = $this->resolveDumpFile();

        if (! $targetFile) {
            return self::FAILURE;
        }

        $dbName = $config['database'] ?? '';
        $fileSize = $this->formatBytes(File::size($targetFile));
        $fileName = basename($targetFile);

        $this->warn("Target Database: [{$dbName}] on connection [{$connectionName}] ({$driver})");
        $this->line("Dump File:       {$fileName} ({$fileSize})");

        if (! $this->option('force')) {
            if (! $this->confirm("Are you sure you want to restore? This will overwrite data in database [{$dbName}]!", false)) {
                $this->comment('Restore cancelled.');

                return self::SUCCESS;
            }
        }

        $this->info("Starting restoration from [{$fileName}]...");

        try {
            if (in_array($driver, ['mysql', 'mariadb'])) {
                $this->restoreMySql($config, $targetFile);
            } elseif ($driver === 'sqlite') {
                $this->restoreSqlite($config, $targetFile);
            } else {
                $this->error("Driver [{$driver}] is not currently supported for automated restore.");

                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('Failed to restore database: '.$e->getMessage());

            return self::FAILURE;
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $this->newLine();
        $this->info("✓ Database restored successfully in {$elapsed}s!");

        return self::SUCCESS;
    }

    /**
     * Resolve the dump file to restore from.
     */
    protected function resolveDumpFile(): ?string
    {
        $fileOption = $this->option('file');
        $dumpDir = database_path('dumps');

        if ($fileOption) {
            // Check direct path or path relative to project
            if (File::exists($fileOption)) {
                return realpath($fileOption);
            }

            // Check inside database/dumps
            $inDumpDir = $dumpDir.DIRECTORY_SEPARATOR.$fileOption;
            if (File::exists($inDumpDir)) {
                return realpath($inDumpDir);
            }

            $this->error("Specified dump file not found: [{$fileOption}]");

            return null;
        }

        // Scan database/dumps/
        if (! File::isDirectory($dumpDir)) {
            $this->error("Dump directory does not exist at [{$dumpDir}].");

            return null;
        }

        $files = File::files($dumpDir);
        $dumpFiles = [];

        foreach ($files as $file) {
            $ext = $file->getExtension();
            $filename = $file->getFilename();
            if ($ext === 'sql' || str_ends_with($filename, '.sql.gz')) {
                $dumpFiles[] = $file;
            }
        }

        if (empty($dumpFiles)) {
            $this->error("No dump files found in [database/dumps/]. Run 'php artisan db:dump' first.");

            return null;
        }

        // Sort latest first
        usort($dumpFiles, fn ($a, $b) => $b->getMTime() <=> $a->getMTime());

        $choices = [];
        $map = [];

        foreach ($dumpFiles as $file) {
            $name = $file->getFilename();
            $size = $this->formatBytes($file->getSize());
            $date = date('Y-m-d H:i:s', $file->getMTime());
            $label = "{$name} ({$size} - {$date})";
            $choices[] = $label;
            $map[$label] = $file->getRealPath();
        }

        $selectedLabel = $this->choice('Select a dump file to restore:', $choices, 0);

        return $map[$selectedLabel] ?? null;
    }

    /**
     * Restore MySQL / MariaDB database.
     */
    protected function restoreMySql(array $config, string $filePath): void
    {
        $clientBinary = $this->findBinary(['mariadb', 'mysql']);
        if (! $clientBinary) {
            throw new \RuntimeException("Neither 'mariadb' nor 'mysql' client binary was found in PATH.");
        }

        $args = [$clientBinary];

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

        $args[] = $database;

        $env = [];
        $password = (string) ($config['password'] ?? '');
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        $this->executeRestoreProcess($args, $env, $filePath);
    }

    /**
     * Restore SQLite database.
     */
    protected function restoreSqlite(array $config, string $filePath): void
    {
        $dbPath = $config['database'] ?? '';
        if (empty($dbPath)) {
            throw new \RuntimeException('SQLite database path is not configured.');
        }

        $sqliteBinary = $this->findBinary(['sqlite3']);
        if (! $sqliteBinary) {
            throw new \RuntimeException("'sqlite3' binary was not found in PATH.");
        }

        $args = [$sqliteBinary, $dbPath];
        $this->executeRestoreProcess($args, [], $filePath);
    }

    /**
     * Execute restore process by streaming dump file contents into client STDIN.
     */
    protected function executeRestoreProcess(array $command, array $env, string $filePath): void
    {
        $isGzip = str_ends_with($filePath, '.gz');
        $process = new Process($command, base_path(), $env, null, 0);

        $process->setInput($this->createStreamIterator($filePath, $isGzip));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException("Restore process failed (exit code {$process->getExitCode()}): ".trim($process->getErrorOutput()));
        }
    }

    /**
     * Create a generator iterator for streaming file content to process STDIN.
     */
    protected function createStreamIterator(string $filePath, bool $isGzip): \Generator
    {
        $handle = $isGzip ? gzopen($filePath, 'rb') : fopen($filePath, 'rb');
        if ($handle === false) {
            throw new \RuntimeException("Unable to open dump file for reading: [{$filePath}]");
        }

        try {
            while (! ($isGzip ? gzeof($handle) : feof($handle))) {
                $chunk = $isGzip ? gzread($handle, 65536) : fread($handle, 65536);
                if ($chunk !== false && $chunk !== '') {
                    yield $chunk;
                }
            }
        } finally {
            if ($isGzip) {
                gzclose($handle);
            } else {
                fclose($handle);
            }
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
