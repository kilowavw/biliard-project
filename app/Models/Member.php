<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $primaryKey = 'id_member';
    public $incrementing = false; // karena bukan auto increment
    protected $keyType = 'string';

    protected $fillable = [
        'id_member',
        'nama_member',
        'no_telp',
        'tgl_bergabung',
        'status',
    ];

    /**
     * Event saat membuat member baru.
     * Generate kode otomatis ID Member.
     */
    protected static function booted()
    {
        static::creating(function ($member) {
            if (empty($member->id_member)) {
                do {
                    // Format: BLxxxx (contoh: BL0007)
                    $kode = 'BL' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                } while (self::where('id_member', $kode)->exists()); // pastikan unik

                $member->id_member = $kode;
            }
        });
    }
    protected $casts = [
        'aktif' => 'boolean',
    ];
}
