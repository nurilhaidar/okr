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
        Schema::create('delegation_target', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegation_id')->constrained('delegation')->cascadeOnDelete();
            $table->morphs('target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegation_target');
    }
};
