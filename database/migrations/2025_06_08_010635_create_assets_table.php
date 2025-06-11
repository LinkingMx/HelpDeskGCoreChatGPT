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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->string('serial_number')->unique()->nullable();
            $table->foreignId('asset_type_id')->constrained('asset_types');
            $table->foreignId('asset_status_id')->constrained('asset_statuses');
            $table->string('assigned_to')->nullable();
            $table->foreignId('client_id')->nullable()->constrained('clients');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 8, 2)->nullable();
            $table->date('warranty_expires_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
