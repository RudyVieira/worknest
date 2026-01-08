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
        Schema::create('space_equipment', function (Blueprint $table) {
            $table->foreignId('space_id')->constrained('spaces');
            $table->foreignId('equipment_type_id')->constrained('equipment_types');
            $table->integer('quantity');
            $table->primary(['space_id', 'equipment_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_equipment');
    }
};
