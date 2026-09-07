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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('zip_code')->nullable();
            $table->text('address')->nullable();

            // Location
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            // Load Types
            $table->json('load_types')->nullable();

            // POA
            $table->boolean('poa')
                ->default(false);

            $table->string('poa_document')
                ->nullable();

            // Contact Information
            $table->json('emails')->nullable();
            $table->json('owners')->nullable();
            $table->json('phones')->nullable();

            // Status
            $table->boolean('status')
                ->default(true);

            // User Audit
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
        Schema::dropIfExists('clients');
    }
};
