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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('supplier')->nullable()->after('brand_id');
            $table->string('invoice_number')->nullable()->after('supplier');
            $table->string('model')->nullable()->after('invoice_number');
            // Cambiar assigned_to para que sea una clave foránea a usuarios
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null')->after('model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['assigned_user_id']);
            $table->dropColumn(['supplier', 'invoice_number', 'model', 'assigned_user_id']);
        });
    }
};
