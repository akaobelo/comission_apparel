<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateSqliteToMysql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-sqlite-to-mysql';

    protected $description = 'Migrate data from the default SQLite database to the MySQL database';

    public function handle()
    {
        $this->info('Starting database migration from SQLite to MySQL...');

        // Force SQLite configuration to point directly to the sqlite file
        // ignoring what is currently set in the .env for DB_DATABASE
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        // Connect to both databases
        try {
            $sqlite = \DB::connection('sqlite');
            $mysql = \DB::connection('mysql');
        } catch (\Exception $e) {
            $this->error('Failed to connect to databases. Make sure your .env has correct MySQL credentials and DB_CONNECTION=mysql');
            $this->error($e->getMessage());
            return;
        }

        // Get all tables from SQLite
        $tables = $sqlite->select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
        
        // Disable foreign key checks on MySQL
        $mysql->statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            $tableName = $table->name;
            $this->info("Migrating table: {$tableName}");

            // Empty the table in MySQL first to prevent duplicate key errors
            try {
                $mysql->table($tableName)->truncate();
            } catch (\Exception $e) {
                $this->error("Failed to truncate table {$tableName}. Did you run `php artisan migrate` on MySQL first?");
                $mysql->statement('SET FOREIGN_KEY_CHECKS=1;');
                return;
            }

            // Get data from SQLite
            $rows = $sqlite->table($tableName)->get();
            $data = array_map(function ($row) {
                return (array) $row;
            }, $rows->toArray());

            // Insert in chunks of 500
            foreach (array_chunk($data, 500) as $chunk) {
                $mysql->table($tableName)->insert($chunk);
            }
        }

        // Enable foreign key checks on MySQL
        $mysql->statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Migration completed successfully!');
    }
}
