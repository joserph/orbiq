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
        Schema::create('logistics_companies', function (Blueprint $table) {
            $table->id();

            // General Information
            $table->string('name');
            $table->string('company_type');
            $table->string('web')->nullable();
            $table->string('ruc', 50)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            // Address
            $table->string('address')->nullable();

            // Location
            $table->foreignId('country_id')->nullable();
            $table->foreignId('state_id')->nullable();
            $table->foreignId('city_id')->nullable();

            // Branding
            $table->string('logo')->nullable();

            // Contact Information
            $table->json('emails')->nullable();
            $table->json('phones')->nullable();

            // Status
            $table->boolean('status')->default(true);

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
        Schema::dropIfExists('logistics_companies');
    }
};
