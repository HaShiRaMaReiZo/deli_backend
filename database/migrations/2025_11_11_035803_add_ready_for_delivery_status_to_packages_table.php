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
        // Modify the enum to add 'ready_for_delivery' status
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'ready_for_delivery' from enum
        DB::statement("ALTER TABLE packages MODIFY COLUMN status ENUM(
            'registered',
            'arrived_at_office',
            'assigned_to_rider',
            'picked_up',
            'on_the_way',
            'delivered',
            'contact_failed',
            'return_to_office',
            'cancelled'
        ) DEFAULT 'registered'");
    }
};
