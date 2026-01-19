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
        Schema::create('delegation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegation_type_id')->constrained('delegation_type')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->nullable();
            $table->foreignId('parent_objective_id')->constrained('objective')->cascadeOnDelete();
            $table->foreignId('child_objective_id')->constrained('objective')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation');
    }
};
