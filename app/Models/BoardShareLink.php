<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardShareLink extends Model
{
    use HasFactory;

    protected $fillable = ['board_id', 'created_by', 'token', 'status'];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateToken()
    {
        return bin2hex(random_bytes(16));
    }
}
