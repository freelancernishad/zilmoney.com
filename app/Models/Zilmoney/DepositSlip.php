<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositSlip extends Model
{
    use HasFactory;

    protected $table = 'deposit_slips';

    protected $fillable = [
        'company_id',
        'account_id',
        'deposit_from',
        'date',
        'ref_id',
        'memo',
        'blank_deposit_slip',
        'cash_items',
        'check_items',
    ];

    protected $casts = [
        'date' => 'date',
        'blank_deposit_slip' => 'boolean',
        'cash_items' => 'array',
        'check_items' => 'array',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
