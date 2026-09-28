<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlightCoordination extends Model
{
    use HasFactory;
    use HasUserAudit;

    protected $table = 'flight_coordinations';

    protected $fillable = [
        'flight_id',
        'hawb',
        'fb',
        'hb',
        'qb',
        'eb',
        'db',
        'fb_r',
        'hb_r',
        'qb_r',
        'eb_r',
        'db_r',
        'returns',
        'client_id',
        'farm_id',
        'marketer_id',
        'varieties',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'fb' => 'integer',
            'hb' => 'integer',
            'qb' => 'integer',
            'eb' => 'integer',
            'db' => 'integer',

            'fb_r' => 'integer',
            'hb_r' => 'integer',
            'qb_r' => 'integer',
            'eb_r' => 'integer',
            'db_r' => 'integer',

            'returns' => 'integer',

            'fulls' => 'decimal:3',
            'pieces' => 'integer',
            'fulls_r' => 'decimal:3',
            'pieces_r' => 'integer',
            'missing' => 'integer',

            'varieties' => 'array',
        ];
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function marketer(): BelongsTo
    {
        return $this->belongsTo(Commercializer::class, 'marketer_id');
    }
}
