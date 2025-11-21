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
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Make status nullable
            DB::statement('ALTER TABLE packages ALTER COLUMN status DROP NOT NULL');
            // Remove default value
            DB::statement("ALTER TABLE packages ALTER COLUMN status DROP DEFAULT");
        } else {
            // MySQL: Make status enum nullable
            // First, we need to modify the enum to allow NULL
            // This is tricky with MySQL enums, so we'll use raw SQL
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
            ) NULL DEFAULT NULL");
        }
        
        // Update existing drafts to have null status
        DB::table('packages')
            ->where('is_draft', true)
            ->whereNotNull('status')
            ->update(['status' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Make status NOT NULL with default
            DB::statement("ALTER TABLE packages ALTER COLUMN status SET DEFAULT 'registered'");
            DB::statement('ALTER TABLE packages ALTER COLUMN status SET NOT NULL');
        } else {
            // MySQL: Make status NOT NULL with default
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
            ) NOT NULL DEFAULT 'registered'");
        }
    }
};
