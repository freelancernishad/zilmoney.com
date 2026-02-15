<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class PlaidItem extends Model
{
    protected $fillable = [
        'user_id',
        'access_token',
        'item_id',
        'institution_id',
        'institution_name',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'plaid_item_id');
    }
    //
}
