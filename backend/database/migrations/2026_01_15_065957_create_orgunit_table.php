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
        Schema::create('orgunit', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('custom_type', 100)->nullable();
            $table->foreignId('orgunit_type_id')->nullable()->constrained('orgunit_type')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('orgunit')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orgunit');
    }
};
