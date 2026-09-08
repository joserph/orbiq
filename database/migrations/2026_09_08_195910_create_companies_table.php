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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // General Information
            $table->string('name');
            $table->string('legal_name')->nullable();

            // Web and Contact Information
            $table->string('web')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Operational Address
            $table->string('address')->nullable();
            $table->string('zip_code', 20)->nullable();

            // Location
            $table->foreignId('country_id')->nullable();
            $table->foreignId('state_id')->nullable();
            $table->foreignId('city_id')->nullable();

            // Legal Information
            $table->string('legal_address')->nullable();

            // Contact Information
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            // Branding
            $table->string('logo')->nullable();

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
        Schema::dropIfExists('companies');
    }
};
