<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Card extends Model
{
    use HasFactory;

    protected $table = 'cards';

    protected $fillable = [
        'company_id',
        'holder_name',
        'type',
        'limit',
        'limit_type',
        'number',
        'expiry',
        'status',
    ];

    protected $casts = [
        'limit' => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }
}
