<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelayanan extends Model
{
    use HasFactory;

    protected $table = 'pelayanans';

    protected $fillable = [
    'nama',
    'kategori', 
    'harga', 
    'gambar'
];
}
