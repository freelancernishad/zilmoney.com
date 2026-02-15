<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersonalInfo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'display_name',
        'ssn',
        'date_of_birth',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postal_code',
        'country',
    ];
    
    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
