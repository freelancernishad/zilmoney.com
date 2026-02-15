<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Attachment extends Model
{
    use HasFactory;

    protected $table = 'company_payment_attachments'; // Maps to company_payment_attachments

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
        return $this->belongsTo(User::class);
    }
}
