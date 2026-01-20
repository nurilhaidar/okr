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
        Schema::create('okr', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('weight', 5, 4);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['owner_type', 'owner_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('okr');
    }
};
