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

class Farm extends Model
{
    use HasFactory;
    use HasUserAudit;

    protected $fillable = [
        'name',
        'trade_name',
        'ruc',
        'web',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'emails',
        'phones',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'emails' => 'array',
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

    public function flowerVarieties(): BelongsToMany
    {
        return $this->belongsToMany(FlowerVariety::class)
            ->withTimestamps();
    }
}
