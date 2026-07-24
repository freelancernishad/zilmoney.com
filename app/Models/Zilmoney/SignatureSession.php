<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SignatureSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'token',
        'type',
        'status',
        'email',
        'ip_address',
        'user_agent',
        'signed_at',
        'expires_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
