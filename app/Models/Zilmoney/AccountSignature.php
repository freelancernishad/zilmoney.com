<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class AccountSignature extends Model
{
    protected $fillable = [
        'account_id',
        'path',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
