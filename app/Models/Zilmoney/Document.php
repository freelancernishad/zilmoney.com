<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $table = 'company_documents'; // Maps to company_documents

    protected $fillable = [
        'company_id',
        'formation_document',
        'ownership_document',
        'principal_officer_id',
        'supporting_documents',
    ];

    protected $casts = [
        'supporting_documents' => 'array',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }
}
