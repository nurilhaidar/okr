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
        Schema::create('objective', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->decimal('weight', 5, 4);
            $table->enum('target_type', ['numeric', 'binary']);
            $table->float('target_value');
            $table->dateTime('deadline');
            $table->foreignId('tracker')->constrained('employee')->cascadeOnDelete();
            $table->foreignId('approver')->constrained('employee')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objective');
    }
};
