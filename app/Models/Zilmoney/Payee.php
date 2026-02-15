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
        'entity_type',
        'company_name',
        'address',
        'bank_account_details',
    ];
    
    protected $casts = [
        'address' => 'array',
        'bank_account_details' => 'array',
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
