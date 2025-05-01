<?php

declare(strict_types=1);

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
        Schema::table('users', function (Blueprint $table) {
            // Add nullable client_id foreign key with cascade on delete
            $table->foreignId('client_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            
            // Add nullable department_id foreign key with restrict on delete
            $table->foreignId('department_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            
            // Add indexes on both columns for better query performance
            $table->index('client_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['client_id']);
            $table->dropForeign(['department_id']);
            
            // Drop indexes
            $table->dropIndex(['client_id']);
            $table->dropIndex(['department_id']);
            
            // Drop columns
            $table->dropColumn('client_id');
            $table->dropColumn('department_id');
        });
    }
};