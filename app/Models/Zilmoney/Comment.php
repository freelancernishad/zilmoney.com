<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'company_payment_comments'; // Maps to company_payment_comments

    protected $fillable = [
        'company_payment_id',
        'user_id',
        'comment',
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
