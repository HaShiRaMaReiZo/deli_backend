<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear 
                            {--force : Force clear without confirmation (for non-interactive use)}
                            {--keep-users : Keep users table (default: true)}
                            {--clear-riders : Also clear riders table}
                            {--clear-merchants : Also clear merchants table}
                            {--clear-users : Clear all non-admin users}
                            {--reseed : Re-seed users after clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all data from database tables (keeps structure)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  WARNING: This will DELETE ALL DATA from the database! Continue?', false)) {
                $this->info('Operation cancelled.');
                return Command::FAILURE;
            }
        } else {
            $this->warn('⚠️  Force mode: Clearing database without confirmation...');
        }

        try {
            DB::beginTransaction();

            $driver = DB::getDriverName();
            
            // Disable foreign key checks
            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = replica;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // Clear all data tables (in order to respect foreign keys)
            $tables = [
                'package_status_histories',
                'rider_assignments',
                'delivery_proofs',
                'cod_collections',
                'financial_transactions',
                'rider_locations',
                'notifications',
                'fcm_tokens',
                'packages',
            ];

            $this->info('Clearing data tables...');
            foreach ($tables as $table) {
                DB::table($table)->truncate();
                $this->line("  ✓ Cleared: {$table}");
            }

            // Optionally clear riders
            if ($this->option('clear-riders')) {
                DB::table('riders')->truncate();
                $this->line("  ✓ Cleared: riders");
            }

            // Optionally clear merchants
            if ($this->option('clear-merchants')) {
                DB::table('merchants')->truncate();
                $this->line("  ✓ Cleared: merchants");
            }

            // Optionally clear all users except super_admin
            if ($this->option('clear-users')) {
                $deleted = DB::table('users')
                    ->where('role', '!=', 'super_admin')
                    ->delete();
                $this->line("  ✓ Deleted {$deleted} non-admin users");
            }

            // Re-enable foreign key checks
            if ($driver === 'pgsql') {
                DB::statement('SET session_replication_role = DEFAULT;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            $this->info('✅ Database cleared successfully!');

            // Optionally re-seed users
            if ($this->option('reseed')) {
                $this->info('Re-seeding users...');
                $this->call('db:seed', ['--class' => 'OfficeUserSeeder', '--force' => true]);
                $this->info('✅ Users re-seeded!');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error clearing database: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
