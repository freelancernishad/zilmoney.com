<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class BusinessDetail extends Model
{
    use HasFactory;

    protected $table = 'companies'; // Maps to companies

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'legal_business_name',
        'dba',
        'entity_type',
        'country',
        'phone_number',
        'website',
        'email',
        'business_in',
        'industry',
        'description',
        'formation_date',
        'verification_photo_id',
        'tax_id',
        'physical_address',
        'legal_registered_address',
        'title',
        'control_person',
        'beneficial_owner',
        'percentage_ownership',
    ];
    
    protected $casts = [
        'formation_date' => 'date',
        'control_person' => 'boolean',
        'beneficial_owner' => 'boolean',
        'physical_address' => 'array',
        'legal_registered_address' => 'array',
    ];

    protected $appends = ['controllers'];

    public function getControllersAttribute()
    {
        return $this->relatedControllers->where('is_individual_owner', false)->values();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedControllers()
    {
        return $this->hasMany(Controller::class, 'company_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'company_id');
    }

    public function accounts()
    {
        return $this->hasMany(Account::class, 'company_id');
    }

    public function payees()
    {
        return $this->hasMany(Payee::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'company_id');
    }

    // Accessors for Template Compatibility
    public function getAddressAttribute()
    {
        return $this->legal_registered_address['line1'] ?? $this->legal_registered_address['address1'] ?? '';
    }

    public function getCityAttribute()
    {
        return $this->legal_registered_address['city'] ?? '';
    }

    public function getStateAttribute()
    {
        return $this->legal_registered_address['region'] ?? $this->legal_registered_address['state'] ?? '';
    }

    public function getZipAttribute()
    {
        return $this->legal_registered_address['postal_code'] ?? $this->legal_registered_address['zip'] ?? '';
    }
}
