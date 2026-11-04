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
        Schema::create('pallet_details', function (Blueprint $table) {
            $table->id();

            // Cantidades
            $table->unsignedInteger('quantity')->default(0);

            $table->unsignedInteger('fb')->default(0);
            $table->unsignedInteger('hb')->default(0);
            $table->unsignedInteger('qb')->default(0);
            $table->unsignedInteger('eb')->default(0);
            $table->unsignedInteger('db')->default(0);

            // Indicador de piso
            $table->boolean('piso')->default(false);

            // Relaciones
            $table->foreignId('pallet_id')
                ->constrained('pallets')
                ->cascadeOnDelete();

            $table->foreignId('load_coordination_id')
                ->constrained('load_coordinations')
                ->cascadeOnDelete();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Auditoría
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Una coordinación solo puede tener un detalle
            // por paleta
            $table->unique(
                ['pallet_id', 'load_coordination_id'],
                'pallet_details_pallet_coordination_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pallet_details');
    }
};
