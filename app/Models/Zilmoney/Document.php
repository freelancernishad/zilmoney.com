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
        'type',
        'file_path',
        'original_name',
        'mime_type',
        'status',
        'metadata',
    ];
    
    protected $casts = [
        'metadata' => 'array',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }
}
