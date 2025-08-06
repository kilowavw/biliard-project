<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penyewaan extends Model
{
    protected $fillable = [
        'meja_id',
        'nama_penyewa',
        'durasi_jam',
        'harga_per_jam',
        'kode_kupon',
        'diskon_persen',
        'total_service',
        'service_detail',
        'total_bayar',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'kasir_id'
    ];

    protected $casts = [
        'service_detail' => 'array',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }
}
