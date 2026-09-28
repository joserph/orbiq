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
        Schema::create('flight_coordinations', function (Blueprint $table) {
            $table->id();

            // Flight
            $table->foreignId('flight_id')
                ->constrained('flights')
                ->cascadeOnDelete();
            $table->string('hawb')->nullable();

            // Coordinated quantities
            $table->unsignedInteger('fb')->default(0);
            $table->unsignedInteger('hb')->default(0);
            $table->unsignedInteger('qb')->default(0);
            $table->unsignedInteger('eb')->default(0);
            $table->unsignedInteger('db')->default(0);

            // Calculated coordinated quantities
            $table->decimal('fulls', 10, 3)
                ->storedAs('
                    COALESCE(fb, 0) * 1 +
                    COALESCE(hb, 0) * 0.5 +
                    COALESCE(qb, 0) * 0.25 +
                    COALESCE(eb, 0) * 0.125 +
                    COALESCE(db, 0) * 0.0625
                ');

            $table->integer('pieces')
                ->storedAs('
                    COALESCE(fb, 0) +
                    COALESCE(hb, 0) +
                    COALESCE(qb, 0) +
                    COALESCE(eb, 0) +
                    COALESCE(db, 0)
                ');

            // Received quantities
            $table->unsignedInteger('fb_r')->default(0);
            $table->unsignedInteger('hb_r')->default(0);
            $table->unsignedInteger('qb_r')->default(0);
            $table->unsignedInteger('eb_r')->default(0);
            $table->unsignedInteger('db_r')->default(0);

            // Calculated received quantities
            $table->decimal('fulls_r', 10, 3)
                ->storedAs('
                    COALESCE(fb_r, 0) * 1 +
                    COALESCE(hb_r, 0) * 0.5 +
                    COALESCE(qb_r, 0) * 0.25 +
                    COALESCE(eb_r, 0) * 0.125 +
                    COALESCE(db_r, 0) * 0.0625
                ');

            $table->integer('pieces_r')
                ->storedAs('
                    COALESCE(fb_r, 0) +
                    COALESCE(hb_r, 0) +
                    COALESCE(qb_r, 0) +
                    COALESCE(eb_r, 0) +
                    COALESCE(db_r, 0)
                ');

            // Returns
            $table->unsignedInteger('returns')->default(0);

            // Calculated missing pieces
            $table->integer('missing')
                ->storedAs('
                    (
                        COALESCE(fb, 0) +
                        COALESCE(hb, 0) +
                        COALESCE(qb, 0) +
                        COALESCE(eb, 0) +
                        COALESCE(db, 0)
                    ) -
                    (
                        COALESCE(fb_r, 0) +
                        COALESCE(hb_r, 0) +
                        COALESCE(qb_r, 0) +
                        COALESCE(eb_r, 0) +
                        COALESCE(db_r, 0)
                    )
                ');

            // Relations
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

            // One or multiple flower varieties
            $table->json('varieties')->nullable();

            // Additional information
            $table->text('observation')->nullable();

            // Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
                
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_coordinations');
    }
};
