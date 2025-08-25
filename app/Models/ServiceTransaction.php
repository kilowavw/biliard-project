<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'kasir_id',
        'total_service',
        'service_detail',
        'kode_kupon',
        'diskon_persen',
        'diskon_amount',
        'total_bayar',
        'payment_method',
        'payment_status',
        'transaction_time',
    ];

    protected $casts = [
        'service_detail' => 'array',
        'transaction_time' => 'datetime',
    ];

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }
}