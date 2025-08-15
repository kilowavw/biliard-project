<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_paket',
        'isi_paket',
        'aktif',
    ];

   // App\Models\Paket.php
    protected $casts = [
        'isi_paket' => 'array', // Ini akan otomatis menangani encode/decode JSON
        'aktif' => 'boolean',
    ];
    
}