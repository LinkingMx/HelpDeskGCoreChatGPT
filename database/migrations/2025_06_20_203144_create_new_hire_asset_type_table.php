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
        Schema::create('new_hire_asset_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_hire_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_type_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['new_hire_id', 'asset_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_hire_asset_type');
    }
};
