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
        Schema::create('approval_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_in_id')->constrained('check_in')->cascadeOnDelete();
            $table->enum('status', ['approved', 'rejected', 'pending']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_log');
    }
};
