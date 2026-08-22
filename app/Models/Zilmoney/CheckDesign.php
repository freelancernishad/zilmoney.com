<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class CheckDesign extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'custom_bg_url',
        'positions',
        'is_active',
    ];

    protected $casts = [
        'positions' => 'array',
        'is_active' => 'boolean',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
