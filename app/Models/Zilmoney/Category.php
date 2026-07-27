<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'company_payment_categories';

    protected $fillable = [
        'company_id',
        'name',
        'type'
    ];

    protected $appends = ['category_type'];

    public function getTypeAttribute($value)
    {
        return $value ?: 'Expense';
    }

    public function getCategoryTypeAttribute()
    {
        return $this->attributes['type'] ?? 'Expense';
    }

    public function business()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'category_id');
    }
}
