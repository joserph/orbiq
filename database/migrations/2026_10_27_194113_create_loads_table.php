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
        Schema::create('loads', function (Blueprint $table) {
            $table->id();

            // Información del embarque
            $table->string('shipment')->nullable();
            $table->unsignedSmallInteger('shipment_year');
            $table->string('bl')->nullable();
            $table->unique(
                ['shipment_year', 'shipment'],
                'loads_shipment_year_number_unique'
            );

            $table->unique('bl', 'loads_bl_unique');
            $table->string('carrier')->nullable();
            $table->date('date');
            $table->date('arrival_date')->nullable();

            // Información logística
            $table->json('thermographs')->nullable();
            $table->string('booking')->nullable();
            $table->foreignId('id_logistic_company')
                ->nullable()
                ->constrained('logistics_companies')
                ->nullOnDelete();

            $table->string('container_number')->nullable();
            $table->string('seal_bottle')->nullable();
            $table->string('seal_cable')->nullable();
            $table->string('seal_sticker')->nullable();

            $table->enum('floor', ['si', 'no'])->nullable();
            $table->string('num_pallets')->nullable();

            // Origin
            $table->foreignId('origin_country_id')->nullable();
            $table->foreignId('origin_city_id')->nullable();

            // Destination
            $table->foreignId('destination_country_id')->nullable();
            $table->foreignId('destination_city_id')->nullable();

            // Información adicional
            $table->string('consignee')->nullable();
            $table->string('entry_number')->nullable();
            $table->string('status')->default('pending');

            // Tipos de caja habilitados
            $table->boolean('fb_status')->default(false);
            $table->boolean('hb_status')->default(true);
            $table->boolean('qb_status')->default(true);
            $table->boolean('eb_status')->default(true);
            $table->boolean('db_status')->default(false);

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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loads');
    }
};
