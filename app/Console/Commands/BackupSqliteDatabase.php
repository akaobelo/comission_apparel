<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupSqliteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup-sqlite
                            {--keep=14 : Number of days to keep local backups}
                            {--s3 : Upload the compressed backup to the configured S3 disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a safe, transactionally consistent hot backup of the SQLite database using VACUUM INTO';

    public function handle(): int
    {
        $connection = config('database.default');
        if ($connection !== 'sqlite') {
            $this->warn("Default database connection is '{$connection}', not 'sqlite'. Proceeding using 'sqlite' connection...");
        }

        $dbPath = config('database.connections.sqlite.database');
        if (!$dbPath || $dbPath === ':memory:' || !File::exists($dbPath)) {
            $this->error("SQLite database file not found at: {$dbPath}");
            return self::FAILURE;
        }

        $backupDir = storage_path('app/private/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_His');
        $rawBackupPath = $backupDir . DIRECTORY_SEPARATOR . "database_{$timestamp}.sqlite";
        $gzBackupPath = $rawBackupPath . '.gz';

        $this->info("Creating hot SQLite snapshot via VACUUM INTO...");

        try {
            // VACUUM INTO creates a transactionally consistent snapshot even while WAL writes are active
            $escapedPath = str_replace("'", "''", $rawBackupPath);
            DB::connection('sqlite')->statement("VACUUM INTO '{$escapedPath}'");

            // Compress the snapshot with gzip to save disk & S3 space
            $this->compressFile($rawBackupPath, $gzBackupPath);
            File::delete($rawBackupPath);

            $sizeMb = round(File::size($gzBackupPath) / 1024 / 1024, 2);
            $this->info("Backup saved locally: {$gzBackupPath} ({$sizeMb} MB)");

            // Upload to S3 if --s3 flag is passed or AWS_BUCKET is configured
            if ($this->option('s3') || !empty(config('filesystems.disks.s3.bucket'))) {
                $s3Key = 'backups/sqlite/' . basename($gzBackupPath);
                $stream = fopen($gzBackupPath, 'r');
                Storage::disk('s3')->put($s3Key, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                $this->info("Backup uploaded to S3: s3://" . config('filesystems.disks.s3.bucket') . "/{$s3Key}");
            }

            // Prune old local backups
            $keepDays = max(1, (int) $this->option('keep'));
            $this->pruneOldBackups($backupDir, $keepDays);

            return self::SUCCESS;
        } catch (\Throwable $e) {
            if (File::exists($rawBackupPath)) {
                File::delete($rawBackupPath);
            }
            $this->error('SQLite backup failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Stream-compress a file using gzip without loading the entire database into memory.
     */
    protected function compressFile(string $source, string $destination): void
    {
        $in = fopen($source, 'rb');
        if ($in === false) {
            throw new \RuntimeException("Unable to open source backup file for compression: {$source}");
        }

        $out = gzopen($destination, 'wb9');
        if ($out === false) {
            fclose($in);
            throw new \RuntimeException("Unable to open destination gzip file: {$destination}");
        }

        while (!feof($in)) {
            $chunk = fread($in, 524288); // 512 KB chunks
            if ($chunk !== false) {
                gzwrite($out, $chunk);
            }
        }

        fclose($in);
        gzclose($out);
    }

    /**
     * Delete local backup files older than $keepDays.
     */
    protected function pruneOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $pruned = 0;

        foreach (File::glob($backupDir . DIRECTORY_SEPARATOR . 'database_*.sqlite*') as $file) {
            if (File::lastModified($file) < $cutoff) {
                File::delete($file);
                $pruned++;
            }
        }

        if ($pruned > 0) {
            $this->info("Pruned {$pruned} local backup(s) older than {$keepDays} days.");
        }
    }
}
