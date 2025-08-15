<?php

// File: app/Models/Kupon.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'diskon_persen',
        'kadaluarsa',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean', // Ini penting untuk toggle
        'kadaluarsa' => 'date', // <<< PASTIKAN INI ADA DAN BENAR!
    ];
}