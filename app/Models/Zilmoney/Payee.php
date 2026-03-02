<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payee extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'payee_name',
        'nick_name',
        'email',
        'phone_number',
        'payee_id_account_number',
        'entity_type',
        'company_name',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
        'bank_account_holder_name',
        'bank_routing_number',
        'bank_account_number',
        'bank_account_type',
    ];

    public function company()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
