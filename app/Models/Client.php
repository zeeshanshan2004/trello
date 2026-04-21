<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'name',
        'email',
        'father_name',
        'phone',
        'image_path',
    ];

    /**
     * Get the workspace that owns the client.
     */
    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

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
