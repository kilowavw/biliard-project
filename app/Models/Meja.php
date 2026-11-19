<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Meja extends Model
{
    protected $fillable = ['nama_meja', 'status', 'reservation_token', 'reservation_expires_at'];

    protected $casts = [
        'reservation_expires_at' => 'datetime',
    ];

    public function penyewaans()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
