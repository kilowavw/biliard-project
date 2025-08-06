<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Meja extends Model
{
    protected $fillable = ['nama_meja', 'status'];

    public function penyewaans()
    {
        return $this->hasMany(Penyewaan::class);
    }
}
