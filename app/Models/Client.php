<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'father_name',
        'phone',
        'image_path',
    ];

    /**
     * Get the boards associated with the client.
     */
    public function boards(): HasMany
    {
        return $this->hasMany(Board::class);
    }

    /**
     * Get the cards associated with the client.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
