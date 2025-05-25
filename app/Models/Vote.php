<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $fillable = [
        'winner_id',
        'loser_id',
        'voted_at'
    ];

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Number::class, 'winner_id');
    }

    public function loser(): BelongsTo
    {
        return $this->belongsTo(Number::class, 'loser_id');
    }
}
