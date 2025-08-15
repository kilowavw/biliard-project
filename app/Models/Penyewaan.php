<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    use HasFactory;

    protected $fillable = [
        'meja_id', 'nama_penyewa', 'durasi_jam', 'harga_per_jam',
        'kode_kupon', 'diskon_persen', 'total_service', 'service_detail',
        'total_bayar', 'waktu_mulai', 'waktu_selesai', 'status', 'kasir_id',
        'paket_id', // Tambahkan ini
    ];

    protected $casts = [
        'durasi_jam' => 'float',
        'harga_per_jam' => 'integer',
        'total_service' => 'integer',
        'diskon_persen' => 'float',
        'total_bayar' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'service_detail' => 'array',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function paket() // Relasi ke model Paket
    {
        return $this->belongsTo(Paket::class);
    }
}