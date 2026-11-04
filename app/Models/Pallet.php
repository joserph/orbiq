<?php

namespace App\Models;

use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pallet extends Model
{
    use HasFactory, HasUserAudit;

    protected $fillable = [
        'counter',
        'number',
        'quantity',
        'usda',
        'load_id',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'usda' => 'boolean',
        ];
    }

    public function shipmentLoad(): BelongsTo
    {
        return $this->belongsTo(Load::class, 'load_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PalletDetail::class);
    }


    public static function generateNumber(int $loadId): array
    {
        $load = Load::findOrFail($loadId);

        $lastCounter = static::where('load_id', $loadId)
            ->get()
            ->max(fn ($pallet) => (int) $pallet->counter) ?? 0;

        $counter = $lastCounter + 1;

        return [
            'counter' => (string) $counter,
            'number' => $load->shipment . '-' . $counter,
        ];
    }

    public function refreshQuantity(): void
    {
        $this->quantity = $this->details()->sum('quantity');

        $this->saveQuietly();
    }

}
