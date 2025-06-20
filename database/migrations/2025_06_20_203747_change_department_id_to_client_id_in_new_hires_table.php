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
        Schema::table('new_hires', function (Blueprint $table) {
            // Eliminar la foreign key de department_id
            $table->dropForeign(['department_id']);

            // Renombrar la columna de department_id a client_id
            $table->renameColumn('department_id', 'client_id');

            // Añadir nueva foreign key para client_id
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('new_hires', function (Blueprint $table) {
            // Eliminar la foreign key de client_id
            $table->dropForeign(['client_id']);

            // Renombrar la columna de client_id a department_id
            $table->renameColumn('client_id', 'department_id');

            // Añadir nueva foreign key para department_id
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }
};
