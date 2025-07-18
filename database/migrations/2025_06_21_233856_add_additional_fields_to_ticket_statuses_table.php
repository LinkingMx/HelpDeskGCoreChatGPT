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
        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('color');
            $table->boolean('is_final')->default(false)->after('is_active');
            $table->text('description')->nullable()->after('is_final');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'is_final', 'description']);
        });
    }
};
