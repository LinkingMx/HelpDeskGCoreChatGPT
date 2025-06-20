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
        Schema::create('new_hires', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_position');
            $table->date('start_date');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('direct_supervisor');
            $table->json('required_asset_types')->nullable(); // Array de IDs de tipos de activos
            $table->text('other_equipment')->nullable();
            $table->text('additional_comments')->nullable();
            $table->boolean('is_replacement')->default(false);
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_hires');
    }
};
