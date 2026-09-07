<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Commercializer extends Model
{
    use HasUserAudit;

    protected $fillable = [
        'type',
        'name',
        'trade_name',
        'emails',
        'phones',
        'staffs',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'emails' => 'array',
            'phones' => 'array',
            'staffs' => 'array',
            'status' => 'boolean',
        ];
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }
}
