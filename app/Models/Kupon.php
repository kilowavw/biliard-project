<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kupon extends Model
{
    protected $fillable = ['kode', 'diskon_persen', 'kadaluarsa', 'aktif'];
}
