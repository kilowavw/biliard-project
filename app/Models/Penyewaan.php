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
<<<<<<< HEAD
        'total_bayar', 'waktu_mulai', 'waktu_selesai', 'status', 'no_telp', 'kasir_id',
        'paket_id', 'pemandu_id', 'is_qris',// Tambahkan ini
=======
        'total_bayar', 'waktu_mulai', 'waktu_selesai', 'status', 'kasir_id',
        'paket_id', 'pemandu_id', 'is_qris','member_id',         
        'diskon_member_persen',
>>>>>>> 3edfb861a8b12a99d28e3b7ac8f3d86bc6a30d88
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
         'is_qris' => 'boolean',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function pemandu()
    {
        return $this->belongsTo(User::class, 'pemandu_id');
    }

    public function paket() // Relasi ke model Paket
    {
        return $this->belongsTo(Paket::class);
    }
    public function member() // Tambah relasi ke Member
    {
        return $this->belongsTo(Member::class);
    }
}