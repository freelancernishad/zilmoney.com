<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Controller extends Model
{
    use HasFactory;

    protected $table = 'company_key_people'; // Maps to company_key_people

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'job_title',
        'email_address',
        'is_individual_owner',
        'percentage_ownership',
    ];
    
    protected $casts = [
        'is_individual_owner' => 'boolean',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }
}
