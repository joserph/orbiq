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
        Schema::create('load_coordinations', function (Blueprint $table) {
            $table->id();

            // Relación con la carga marítima
            $table->foreignId('load_id')
                ->constrained('loads')
                ->cascadeOnDelete();

            // Identificador de la coordinación
            $table->string('hawb');

            // Cajas coordinadas
            $table->unsignedInteger('fb')->default(0);
            $table->unsignedInteger('hb')->default(0);
            $table->unsignedInteger('qb')->default(0);
            $table->unsignedInteger('eb')->default(0);
            $table->unsignedInteger('db')->default(0);

            // Cálculos automáticos de cajas coordinadas
            $table->decimal('fulls', 10, 3)->storedAs(
                'COALESCE(fb, 0) + COALESCE(hb, 0) * 0.5 + ' .
                'COALESCE(qb, 0) * 0.25 + COALESCE(eb, 0) * 0.125 + ' .
                'COALESCE(db, 0) * 0.0625'
            );

            $table->unsignedInteger('pieces')->storedAs(
                'COALESCE(fb, 0) + COALESCE(hb, 0) + ' .
                'COALESCE(qb, 0) + COALESCE(eb, 0) + COALESCE(db, 0)'
            );

            // Cajas recibidas
            $table->unsignedInteger('fb_r')->default(0);
            $table->unsignedInteger('hb_r')->default(0);
            $table->unsignedInteger('qb_r')->default(0);
            $table->unsignedInteger('eb_r')->default(0);
            $table->unsignedInteger('db_r')->default(0);

            // Cálculos automáticos de cajas recibidas
            $table->decimal('fulls_r', 10, 3)->storedAs(
                'COALESCE(fb_r, 0) + COALESCE(hb_r, 0) * 0.5 + ' .
                'COALESCE(qb_r, 0) * 0.25 + COALESCE(eb_r, 0) * 0.125 + ' .
                'COALESCE(db_r, 0) * 0.0625'
            );

            $table->unsignedInteger('pieces_r')->storedAs(
                'COALESCE(fb_r, 0) + COALESCE(hb_r, 0) + ' .
                'COALESCE(qb_r, 0) + COALESCE(eb_r, 0) + COALESCE(db_r, 0)'
            );

            // Retornos y faltantes
            $table->unsignedInteger('returns')->default(0);

            $table->unsignedInteger('missing')->storedAs(
                'GREATEST((' .
                'COALESCE(fb, 0) + COALESCE(hb, 0) + ' .
                'COALESCE(qb, 0) + COALESCE(eb, 0) + COALESCE(db, 0)' .
                ') - (' .
                'COALESCE(fb_r, 0) + COALESCE(hb_r, 0) + ' .
                'COALESCE(qb_r, 0) + COALESCE(eb_r, 0) + COALESCE(db_r, 0)' .
                '), 0)'
            );

            // Relaciones comerciales
            $table->foreignId('client_id')
                ->constrained('clients')
                ->restrictOnDelete();

            $table->foreignId('farm_id')
                ->constrained('farms')
                ->restrictOnDelete();

            $table->foreignId('marketer_id')
                ->nullable()
                ->constrained('commercializers')
                ->nullOnDelete();

            // Información adicional
            $table->json('varieties')->nullable();
            $table->text('observation')->nullable();

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

            // HAWB único dentro de cada carga
            $table->unique(
                ['load_id', 'hawb'],
                'load_coordinations_load_hawb_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('load_coordinations');
    }
};
