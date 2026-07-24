<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'company_payments'; // Maps to company_payments

    protected $fillable = [
        'company_id',
        'account_id',
        'payee_id',
        'pay_from',
        'pay_as',
        'amount',
        'status',
        'issue_date',
        'check_number',
        'invoice_number',
        'payee_id_account_number',
        'category_id',
        'memo',
        'remittance_info',
        'delivery_proof',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'remittance_info' => 'array',
        'delivery_proof' => 'array',
    ];

    protected $appends = [
        'payee_name',
        'signature_image',
        'signature_image_url',
        'company_logo_url',
        'company_name',
        'business_detail',
    ];

    public function businessDetail()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'company_payment_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'company_payment_id');
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class, 'company_payment_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'company_payment_id');
    }

    public function deliveryProofs()
    {
        return $this->hasMany(DeliveryProof::class, 'company_payment_id');
    }

    public function remittances()
    {
        return $this->hasMany(Remittance::class, 'company_payment_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Alias for standardized access
    public function business()
    {
        return $this->belongsTo(BusinessDetail::class, 'company_id');
    }

    // Bank comes from the account
    public function getBankAttribute()
    {
        return $this->account; // The Account model has fields like bank_name, routing_number, etc.
    }

    // Standardized payee name
    public function getPayeeNameAttribute()
    {
        return $this->payee ? $this->payee->payee_name : '';
    }

    // Active signature image path/url for DomPDF (Absolute Path)
    public function getSignatureImageAttribute()
    {
        $signature = $this->account ? $this->account->activeSignature : null;
        if ($signature && $signature->path) {
            if (filter_var($signature->path, FILTER_VALIDATE_URL)) {
                return $signature->path;
            }
            if (file_exists(storage_path('app/public/' . $signature->path))) {
                return storage_path('app/public/' . $signature->path);
            }
            if (file_exists(public_path($signature->path))) {
                return public_path($signature->path);
            }
            return storage_path('app/public/' . $signature->path);
        }
        return null;
    }

    // Active signature image URL for React Frontend (HTTP URL)
    public function getSignatureImageUrlAttribute()
    {
        $signature = $this->account ? $this->account->activeSignature : null;
        if ($signature && $signature->path) {
            if (filter_var($signature->path, FILTER_VALIDATE_URL)) {
                return $signature->path;
            }
            return asset('storage/' . $signature->path);
        }
        return null;
    }

    public function getCompanyLogoUrlAttribute()
    {
        $biz = $this->business;
        if ($biz && $biz->verification_photo_id) {
            $photo = $biz->verification_photo_id;

            if (filter_var($photo, FILTER_VALIDATE_URL)) {
                return $photo;
            }

            if (config('filesystems.default') === 's3' || env('FILESYSTEM_DISK') === 's3') {
                return \Illuminate\Support\Facades\Storage::disk('s3')->url($photo);
            }

            return asset('storage/' . ltrim($photo, '/'));
        }
        return null;
    }

    public function getCompanyNameAttribute()
    {
        $biz = $this->business;
        return $biz ? ($biz->legal_business_name ?? $biz->dba ?? null) : null;
    }

    public function getBusinessDetailAttribute()
    {
        $biz = $this->business;
        if ($biz) {
            $addr = $biz->physical_address;
            $addrStr = null;
            if (is_array($addr)) {
                $parts = array_filter([
                    $addr['address1'] ?? '',
                    $addr['city'] ?? '',
                    isset($addr['state']) ? $addr['state'] . " " . ($addr['zip'] ?? '') : ''
                ]);
                $addrStr = implode(', ', $parts);
            } elseif (is_string($addr)) {
                $addrStr = $addr;
            }

            return [
                'id' => $biz->id,
                'company_name' => $biz->legal_business_name ?? $biz->dba,
                'legal_business_name' => $biz->legal_business_name,
                'verification_photo_id' => $biz->verification_photo_id,
                'address_line1' => $addrStr,
                'physical_address' => $biz->physical_address,
                'phone_number' => $biz->phone_number,
                'email' => $biz->email,
            ];
        }
        return null;
    }

    protected static function booted()
    {
        static::created(function ($payment) {
            $payment->logs()->create([
                'status' => 'Created', // or $payment->status if preferred
                'initiated_by' => auth()->id(),
                'note' => 'Payment created',
                'device_info' => request()->ip(),
            ]);
        });

        static::updated(function ($payment) {
            $changes = [];
            $logStatus = 'updated';

            foreach ($payment->getDirty() as $attribute => $newValue) {
                if ($attribute === 'updated_at' || $attribute === 'created_at') continue;

                $oldValue = $payment->getOriginal($attribute);

                if ($attribute === 'amount') {
                    $changes[] = "Amount is changed {$oldValue} To {$newValue}";
                } elseif ($attribute === 'payee_id') {
                    $oldPayee = \App\Models\Zilmoney\Payee::find($oldValue);
                    $newPayee = \App\Models\Zilmoney\Payee::find($newValue);
                    $oldName = $oldPayee ? $oldPayee->payee_name : '';
                    $newName = $newPayee ? $newPayee->payee_name : '';
                    if ($oldName || $newName) {
                        $changes[] = "Payee is changed {$oldName} to {$newName}";
                    } else {
                        $changes[] = "Payee changed";
                    }
                } elseif ($attribute === 'memo') {
                    if (empty($newValue) && !empty($oldValue)) {
                        $changes[] = "Note is removed";
                    } elseif (empty($oldValue) && !empty($newValue)) {
                        $changes[] = "Note is added";
                    } else {
                        $changes[] = "Note is changed";
                    }
                } elseif ($attribute === 'status') {
                    $logStatus = $newValue;
                    if ($newValue === 'Printed' || $newValue === 'Re-Printed') {
                        $changes[] = "Check paper {$payment->check_number} has been printed";
                    } else {
                        $changes[] = "Status is changed to {$newValue}";
                    }
                } elseif ($attribute === 'check_number') {
                    $changes[] = "Check number is changed {$oldValue} to {$newValue}";
                } else {
                    $attrName = ucwords(str_replace('_', ' ', $attribute));
                    if (!is_array($oldValue) && !is_array($newValue)) {
                        $changes[] = "{$attrName} is changed {$oldValue} to {$newValue}";
                    } else {
                        $changes[] = "{$attrName} is updated";
                    }
                }
            }

            $note = implode(', ', $changes);
            if (empty($note)) {
                $note = 'No changes';
            }

            $payment->logs()->create([
                'status' => $logStatus,
                'initiated_by' => auth()->id(),
                'note' => $note,
                'device_info' => request()->ip(),
            ]);
        });
    }
}
