<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'account_holder_name',
        'account_nick_name',
        'account_number',
        'routing_number',
        'type',
        'balance',
        'plaid_item_id',
        'plaid_account_id',
        'mask',
        'official_name',
        'email',
        'phone_number',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'ach_auth_form',
        'next_check_starting_number',
    ];

    protected $casts = [
        'ach_auth_form' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    public function plaidItem()
    {
        return $this->belongsTo(PlaidItem::class, 'plaid_item_id');
    }

    public function signatures()
    {
        return $this->hasMany(AccountSignature::class);
    }

    public function activeSignature()
    {
        return $this->hasOne(AccountSignature::class)->where('is_primary', true);
    }
}
