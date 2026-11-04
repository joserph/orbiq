<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalletDetail extends Model
{
    use HasFactory, HasUserAudit;

    protected $fillable = [
        'quantity',
        'fb',
        'hb',
        'qb',
        'eb',
        'db',
        'piso',
        'pallet_id',
        'load_coordination_id',
        'farm_id',
        'client_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'fb' => 'integer',
            'hb' => 'integer',
            'qb' => 'integer',
            'eb' => 'integer',
            'db' => 'integer',
            'piso' => 'boolean',
        ];
    }

    public function pallet(): BelongsTo
    {
        return $this->belongsTo(Pallet::class);
    }

    public function loadCoordination(): BelongsTo
    {
        return $this->belongsTo(LoadCoordination::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    protected static function booted(): void
    {
        static::saving(function (PalletDetail $detail): void {
            $detail->quantity =
                (int) $detail->fb +
                (int) $detail->hb +
                (int) $detail->qb +
                (int) $detail->eb +
                (int) $detail->db;
        });

        static::saved(function (PalletDetail $detail): void {
            $detail->pallet?->refreshQuantity();
        });

        static::deleted(function (PalletDetail $detail): void {
            Pallet::find($detail->pallet_id)?->refreshQuantity();
        });
    }
}
