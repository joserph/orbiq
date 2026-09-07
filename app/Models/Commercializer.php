<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Model;

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
}
