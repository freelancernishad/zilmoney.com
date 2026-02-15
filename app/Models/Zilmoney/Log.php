<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Log extends Model
{
    use HasFactory;

    protected $table = 'company_payment_logs'; // Maps to company_payment_logs

    protected $fillable = [
        'company_payment_id',
        'status',
        'initiated_by',
        'device_info',
        'note',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'company_payment_id');
    }

    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
