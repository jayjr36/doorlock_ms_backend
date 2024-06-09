<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Password extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'user_id', 'password', 'expires_at'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return $this->expires_at < Carbon::now();
    }
}
