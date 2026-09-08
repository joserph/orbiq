<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class Company extends Model
{
    use HasFactory;
    use HasUserAudit;

    protected $fillable = [
        'name',
        'legal_name',
        'web',
        'phone',
        'email',
        'address',
        'zip_code',
        'country_id',
        'state_id',
        'city_id',
        'legal_address',
        'contact_email',
        'contact_phone',
        'logo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Location Relationships
    |--------------------------------------------------------------------------
    */

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
