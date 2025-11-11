<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique()->index();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('delivery_address');
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->enum('payment_type', ['cod', 'prepaid'])->default('cod');
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('package_image')->nullable();
            $table->text('package_description')->nullable();
            $table->enum('status', [
                'registered',
                'arrived_at_office',
                'assigned_to_rider',
                'picked_up',
                'on_the_way',
                'delivered',
                'contact_failed',
                'return_to_office',
                'cancelled'
            ])->default('registered');
            $table->foreignId('current_rider_id')->nullable()->constrained('riders')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->integer('delivery_attempts')->default(0);
            $table->text('delivery_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('merchant_id');
            $table->index('status');
            $table->index('current_rider_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
