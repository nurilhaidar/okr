<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replaces owner morph columns with employee_id and orgunit_id columns.
     */
    public function up(): void
    {
        Schema::table('okr', function (Blueprint $table) {
            // Drop the morph columns (owner_id, owner_type)
            $table->dropMorphs('owner');

            // Add employee_id and orgunit_id columns
            $table->foreignId('employee_id')->nullable()->constrained('employee')->nullOnDelete();
            $table->foreignId('orgunit_id')->nullable()->constrained('orgunit')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('okr', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['orgunit_id']);

            // Drop columns
            $table->dropColumn(['employee_id', 'orgunit_id']);

            // Restore morph columns
            $table->morphs('owner');
        });
    }
};
