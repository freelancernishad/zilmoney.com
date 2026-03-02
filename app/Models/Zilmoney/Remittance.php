<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class Remittance extends Model
{
    protected $table = 'company_payment_remittances';

    protected $fillable = [
        'company_payment_id',
        'user_id',
        'invoice_number',
        'item',
        'description',
        'quantity',
        'unit_cost',
        'total',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'company_payment_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
