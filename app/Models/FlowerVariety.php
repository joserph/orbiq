<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUserAudit;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FlowerVariety extends Model
{
    use HasUserAudit;
    protected $fillable = [
        'name',
        'scientific_name',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function farms(): BelongsToMany
    {
        return $this->belongsToMany(Farm::class)
            ->withTimestamps();
    }

}
