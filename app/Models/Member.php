<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 3edfb861a8b12a99d28e3b7ac8f3d86bc6a30d88
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_member',
        'kode_member',
        'email',
        'no_telepon',
        'tanggal_daftar',
        'tanggal_kadaluarsa',
        'status_keanggotaan',
        'diskon_persen',
        'last_payment_date',
        'last_payment_amount',
        'last_payment_method',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'last_payment_date' => 'datetime',
        'diskon_persen' => 'float',
        'last_payment_amount' => 'decimal:2',
    ];

    // Event saat membuat member baru untuk autofill tanggal kadaluarsa
    protected static function booted()
    {
        static::creating(function (Member $member) {
            if (empty($member->tanggal_daftar)) {
                $member->tanggal_daftar = now();
            }
            if (empty($member->tanggal_kadaluarsa)) {
                $member->tanggal_kadaluarsa = $member->tanggal_daftar->addMonth(); // Default 1 bulan
            }
        });
    }

    /**
     * Generate a unique member code.
     * This is a simple example, you might want a more robust solution.
     */
    public static function generateUniqueKodeMember()
    {
        $prefix = 'MBR';
        do {
            $code = $prefix . mt_rand(10000, 99999);
        } while (self::where('kode_member', $code)->exists());
        return $code;
    }
}
>>>>>>> 3edfb861a8b12a99d28e3b7ac8f3d86bc6a30d88
