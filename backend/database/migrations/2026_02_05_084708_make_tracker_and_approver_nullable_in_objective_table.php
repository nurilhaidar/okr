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
        // Drop foreign keys first
        Schema::table('objective', function (Blueprint $table) {
            $table->dropForeign(['tracker']);
            $table->dropForeign(['approver']);
        });

        // Make columns nullable and re-add foreign keys
        Schema::table('objective', function (Blueprint $table) {
            $table->foreignId('tracker')->nullable()->change();
            $table->foreignId('approver')->nullable()->change();
        });

        // Re-add foreign key constraints
        Schema::table('objective', function (Blueprint $table) {
            $table->foreign('tracker')->references('id')->on('employee')->nullOnDelete();
            $table->foreign('approver')->references('id')->on('employee')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign keys
        Schema::table('objective', function (Blueprint $table) {
            $table->dropForeign(['tracker']);
            $table->dropForeign(['approver']);
        });

        // Make columns not nullable
        Schema::table('objective', function (Blueprint $table) {
            $table->foreignId('tracker')->nullable(false)->change();
            $table->foreignId('approver')->nullable(false)->change();
        });

        // Re-add foreign key constraints
        Schema::table('objective', function (Blueprint $table) {
            $table->foreign('tracker')->references('id')->on('employee')->cascadeOnDelete();
            $table->foreign('approver')->references('id')->on('employee')->cascadeOnDelete();
        });
    }
};
