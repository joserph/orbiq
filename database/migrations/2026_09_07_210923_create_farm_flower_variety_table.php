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
        Schema::create('farm_flower_variety', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farm_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('flower_variety_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'farm_id',
                'flower_variety_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('farm_flower_variety');
    }
};
