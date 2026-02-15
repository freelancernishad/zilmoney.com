<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'company_payments'; // Maps to company_payments

    protected $fillable = [
        'company_id',
        'account_id',
        'payee_id',
        'amount',
        'status',
        'issue_date',
        'check_number',
        'memo',
        'remittance_info',
        'delivery_proof',
    ];
    
    protected $casts = [
        'issue_date' => 'date',
        'remittance_info' => 'array',
        'delivery_proof' => 'array',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'company_payment_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'company_payment_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'company_payment_id');
    }

    // Alias for standardized access
    public function business()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    // Bank comes from the account
    public function getBankAttribute()
    {
        return $this->account; // The Account model has fields like bank_name, routing_number, etc.
    }
}
