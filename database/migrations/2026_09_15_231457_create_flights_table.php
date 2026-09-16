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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();

            // AWB Information
            $table->string('awb');
            $table->string('type_awb');

            // Companies
            $table->foreignId('logistics_company_id')
                ->nullable()
                ->constrained('logistics_companies')
                ->nullOnDelete();

            $table->foreignId('airline_id')
                ->nullable()
                ->constrained('airlines')
                ->nullOnDelete();

            // Dates
            $table->date('date')->nullable();
            $table->date('arrival_date')->nullable();

            // Origin
            $table->foreignId('origin_country_id')->nullable();
            $table->foreignId('origin_city_id')->nullable();

            // Destination
            $table->foreignId('destination_country_id')->nullable();
            $table->foreignId('destination_city_id')->nullable();

            // Customs / Consignee
            $table->string('consignee')->nullable();
            $table->string('entry_number')->nullable();

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
        Schema::dropIfExists('flights');
    }
};
