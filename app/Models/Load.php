<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class Load extends Model
{
    protected $fillable = [
        'shipment',
        'shipment_year',
        'bl',
        'carrier',
        'date',
        'arrival_date',
        'thermographs',
        'booking',
        'id_logistic_company',
        'container_number',
        'seal_bottle',
        'seal_cable',
        'seal_sticker',
        'floor',
        'num_pallets',
        'origin_country_id',
        'origin_city_id',
        'destination_country_id',
        'destination_city_id',
        'consignee',
        'entry_number',
        'status',
        'fb_status',
        'hb_status',
        'qb_status',
        'eb_status',
        'db_status',
        // 'id_user',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'arrival_date' => 'date',
            'fb_status' => 'boolean',
            'hb_status' => 'boolean',
            'qb_status' => 'boolean',
            'eb_status' => 'boolean',
            'db_status' => 'boolean',
            'thermographs' => 'array',
        ];
    }

    public function logisticsCompany(): BelongsTo
    {
        return $this->belongsTo(
            LogisticsCompany::class,
            'id_logistic_company'
        );
    }

    // public function user(): BelongsTo
    // {
    //     return $this->belongsTo(User::class, 'id_user');
    // }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    // protected static function booted(): void
    // {
    //     static::creating(function (Load $load) {
    //         if (! $load->date) {
    //             throw ValidationException::withMessages([
    //                 'date' => 'La fecha del embarque es obligatoria.',
    //             ]);
    //         }

    //         DB::transaction(function () use ($load) {
    //             $year = (int) date('Y', strtotime($load->date));

    //             $sequence = LoadShipmentSequence::query()
    //                 ->where('year', $year)
    //                 ->lockForUpdate()
    //                 ->first();

    //             if (! $sequence) {
    //                 LoadShipmentSequence::query()->create([
    //                     'year' => $year,
    //                     'last_number' => 0,
    //                 ]);

    //                 $sequence = LoadShipmentSequence::query()
    //                     ->where('year', $year)
    //                     ->lockForUpdate()
    //                     ->firstOrFail();
    //             }

    //             $sequence->increment('last_number');

    //             $load->shipment_year = $year;
    //             $load->shipment = (string) $sequence->last_number;
    //         });
    //     });
    // }

    protected static function booted(): void
    {
        static::creating(function (Load $load) {
            if (! $load->date) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'date' => 'La fecha del embarque es obligatoria.',
                ]);
            }

            $year = (int) date('Y', strtotime($load->date));

            $load->shipment_year = $year;

            // El número definitivo se asignará dentro de una transacción
            // al guardar el Load, para evitar duplicados.
        });
    }

    public function coordinations(): HasMany
    {
        return $this->hasMany(LoadCoordination::class);
    }

    public function originCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'origin_country_id');
    }

    public function originCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'origin_city_id');
    }

    public function destinationCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'destination_country_id');
    }

    public function destinationCity(): BelongsTo
    {
        return $this->belongsTo(City::class, 'destination_city_id');
    }

}
