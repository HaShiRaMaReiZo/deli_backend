<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Modify the enum to add 'returned_to_merchant' status
            DB::statement("ALTER TABLE packages MODIFY COLUMN status ENUM(
                'registered',
                'arrived_at_office',
                'assigned_to_rider',
                'picked_up',
                'ready_for_delivery',
                'on_the_way',
                'delivered',
                'contact_failed',
                'return_to_office',
                'returned_to_merchant',
                'cancelled'
            ) DEFAULT 'registered'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Update check constraint to include 'returned_to_merchant'
            DB::statement("ALTER TABLE packages DROP CONSTRAINT IF EXISTS packages_status_check");
            DB::statement("ALTER TABLE packages ADD CONSTRAINT packages_status_check CHECK (status IN (
                'registered',
                'arrived_at_office',
                'assigned_to_rider',
                'picked_up',
                'ready_for_delivery',
                'on_the_way',
                'delivered',
                'contact_failed',
                'return_to_office',
                'returned_to_merchant',
                'cancelled'
            ))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'mysql') {
            // MySQL: Remove 'returned_to_merchant' from enum
            DB::statement("ALTER TABLE packages MODIFY COLUMN status ENUM(
                'registered',
                'arrived_at_office',
                'assigned_to_rider',
                'picked_up',
                'ready_for_delivery',
                'on_the_way',
                'delivered',
                'contact_failed',
                'return_to_office',
                'cancelled'
            ) DEFAULT 'registered'");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: Revert check constraint
            DB::statement("ALTER TABLE packages DROP CONSTRAINT IF EXISTS packages_status_check");
            DB::statement("ALTER TABLE packages ADD CONSTRAINT packages_status_check CHECK (status IN (
                'registered',
                'arrived_at_office',
                'assigned_to_rider',
                'picked_up',
                'ready_for_delivery',
                'on_the_way',
                'delivered',
                'contact_failed',
                'return_to_office',
                'cancelled'
            ))");
        }
    }
};
