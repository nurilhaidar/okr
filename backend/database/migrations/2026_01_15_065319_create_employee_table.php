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
        Schema::create('employee', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('username', 255)->unique();
            $table->string('password', 255);
            $table->foreignId('rank_id')->nullable()->constrained('rank')->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('position')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('role')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee');
    }
};
