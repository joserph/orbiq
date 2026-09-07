<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;

class Client extends Model
{
    use HasFactory;
    use HasUserAudit;

    protected $fillable = [
        'name',
        'zip_code',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'load_types',
        'poa',
        'poa_document',
        'emails',
        'owners',
        'phones',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'load_types' => 'array',
            'poa' => 'boolean',
            'emails' => 'array',
            'owners' => 'array',
            'phones' => 'array',
            'status' => 'boolean',
        ];
    }

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

    /*
    |--------------------------------------------------------------------------
    | Commercializers Relationship
    |--------------------------------------------------------------------------
    */

    public function commercializers(): BelongsToMany
    {
        return $this->belongsToMany(Commercializer::class);
    }
}
