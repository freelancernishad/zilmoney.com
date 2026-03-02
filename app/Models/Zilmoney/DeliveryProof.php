<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class DeliveryProof extends Model
{
    protected $table = 'company_payment_delivery_proofs';

    protected $fillable = [
        'company_payment_id',
        'user_id',
        'file_path',
        'original_name',
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
