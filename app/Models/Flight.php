<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class Flight extends Model
{
    use HasFactory;
    use HasUserAudit;

    protected $fillable = [
        'awb',
        'type_awb',
        'logistics_company_id',
        'airline_id',
        'date',
        'arrival_date',
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
        'db_status'
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'arrival_date' => 'date',
            'status' => 'boolean',
            'fb_status' => 'boolean',
            'hb_status' => 'boolean',
            'qb_status' => 'boolean',
            'eb_status' => 'boolean',
            'db_status' => 'boolean',
        ];
    }

    public function logisticsCompany(): BelongsTo
    {
        return $this->belongsTo(LogisticsCompany::class);
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
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

    public function coordinations(): HasMany
    {
        return $this->hasMany(FlightCoordination::class);
    }
}
