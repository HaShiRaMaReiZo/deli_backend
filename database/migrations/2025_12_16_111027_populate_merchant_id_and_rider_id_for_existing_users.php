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
        // Populate merchant_id for users who have a merchant profile
        DB::statement('
            UPDATE users u
            INNER JOIN merchants m ON u.id = m.user_id
            SET u.merchant_id = m.id
            WHERE u.role = "merchant"
        ');

        // Populate rider_id for users who have a rider profile
        DB::statement('
            UPDATE users u
            INNER JOIN riders r ON u.id = r.user_id
            SET u.rider_id = r.id
            WHERE u.role = "rider"
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear merchant_id and rider_id
        DB::table('users')->update([
            'merchant_id' => null,
            'rider_id' => null,
        ]);
    }
};
