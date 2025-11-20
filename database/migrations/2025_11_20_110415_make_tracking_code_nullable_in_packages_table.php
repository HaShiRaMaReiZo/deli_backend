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
            // PostgreSQL: Make tracking_code nullable
            DB::statement('ALTER TABLE packages ALTER COLUMN tracking_code DROP NOT NULL');
        } else {
            // MySQL: Make tracking_code nullable
            Schema::table('packages', function (Blueprint $table) {
                $table->string('tracking_code')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Make tracking_code NOT NULL (but this might fail if there are nulls)
            DB::statement('ALTER TABLE packages ALTER COLUMN tracking_code SET NOT NULL');
        } else {
            // MySQL: Make tracking_code NOT NULL
            Schema::table('packages', function (Blueprint $table) {
                $table->string('tracking_code')->nullable(false)->change();
            });
        }
    }
};
