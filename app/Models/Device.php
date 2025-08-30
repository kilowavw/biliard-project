<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ip_address',
        'last_seen_at',
        'pending_command', // Tambahkan ini
        'command_sent_at', // Tambahkan ini
        'command_executed_at', // Tambahkan ini
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'command_sent_at' => 'datetime',
        'command_executed_at' => 'datetime',
    ];
}