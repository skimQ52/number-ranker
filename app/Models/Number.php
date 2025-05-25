<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Number extends Model
{
    use HasFactory;

    protected $table = 'numbers';
    protected $fillable = [
        'number',
        'elo',
        'wins',
        'losses'
    ];
    private $id;

    public function wins(): HasMany
    {
        return $this->hasMany(Vote::class, 'winner_id');
    }

    public function losses(): HasMany
    {
        return $this->hasMany(Vote::class, 'loser_id');
    }
}
