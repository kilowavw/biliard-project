<?php

// File: app/Models/HargaSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HargaSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'harga_per_jam',        // Pertahankan jika masih digunakan sebagai default/cadangan
        'harga_per_jam_siang',  // Tambahkan ini
        'harga_per_jam_malam',  // Tambahkan ini
    ];
}