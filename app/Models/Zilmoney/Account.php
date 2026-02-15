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
        'address',
        'ach_auth_form',
        'next_check_starting_number',
        'signature_path',
    ];
    
    protected $casts = [
        'address' => 'array',
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

    public function getFormattedAddressAttribute()
    {
        $addr = $this->address;
        if (is_array($addr)) {
            // Plaid address structure or manual structure
            // Manual: line1, city, state, zip
            // Plaid: data.address (sometimes)
            
            $line1 = $addr['street'] ?? $addr['line1'] ?? '';
            $city = $addr['city'] ?? '';
            $state = $addr['region'] ?? $addr['state'] ?? '';
            $zip = $addr['postal_code'] ?? $addr['zip'] ?? '';
            
            return implode(', ', array_filter([$line1, $city, "$state $zip"]));
        }
        return $this->address; // Fallback if string
    }
}
