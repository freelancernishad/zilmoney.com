<?php

namespace App\Models\Zilmoney;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'company_payments'; // Maps to company_payments

    protected $fillable = [
        'email_token',
        'unique_check_id',
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
        'signature_image',
        'signature_image_url',
        'company_name',
        'company_address',
        'company_logo_url',
        'bank_name',
        'bank_routing_number',
        'bank_account_number',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'remittance_info' => 'array',
        'delivery_proof' => 'array',
    ];

    protected $appends = [
        'unique_check_id',
        'payee_name',
        'signature_image',
        'signature_image_url',
        'company_logo_url',
        'company_name',
        'business_detail',
    ];

    public static function generateEmailToken()
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (self::where('email_token', $token)->exists());

        return $token;
    }

    public static function generateUniqueCheckId()
    {
        do {
            $code = 'CHK-' . mt_rand(10000000, 99999999);
        } while (self::where('unique_check_id', $code)->exists());

        return $code;
    }

    public function getUniqueCheckIdAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return 'CHK-' . str_pad((string)$this->id, 8, '0', STR_PAD_LEFT);
    }



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
        // 1. Prefer payment's own saved raw column snapshot
        $rawPath = $this->getRawOriginal('signature_image');
        if (!empty($rawPath)) {
            if (filter_var($rawPath, FILTER_VALIDATE_URL)) return $rawPath;
            if (file_exists(storage_path('app/public/' . $rawPath))) return storage_path('app/public/' . $rawPath);
            if (file_exists(public_path($rawPath))) return public_path($rawPath);
            return storage_path('app/public/' . $rawPath);
        }

        // 2. Fallback to current account activeSignature
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
        // 1. Prefer payment's own saved raw column snapshot URL
        $rawUrl = $this->getRawOriginal('signature_image_url');
        if (!empty($rawUrl)) {
            return $rawUrl;
        }

        $rawPath = $this->getRawOriginal('signature_image');
        if (!empty($rawPath)) {
            if (filter_var($rawPath, FILTER_VALIDATE_URL)) return $rawPath;
            return asset('storage/' . $rawPath);
        }

        // 2. Fallback to current account activeSignature
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
        $rawLogo = $this->getRawOriginal('company_logo_url');
        if (!empty($rawLogo)) {
            return $rawLogo;
        }
        $biz = $this->business;
        return $biz ? get_file_url($biz->verification_photo_id) : null;
    }

    public function getCompanyNameAttribute()
    {
        $rawName = $this->getRawOriginal('company_name');
        if (!empty($rawName)) {
            return $rawName;
        }
        $biz = $this->business;
        return $biz ? ($biz->legal_business_name ?? $biz->dba ?? null) : null;
    }

    public function getBusinessDetailAttribute()
    {
        $rawName = $this->getRawOriginal('company_name');
        $rawAddr = $this->getRawOriginal('company_address');

        if (!empty($rawName) || !empty($rawAddr)) {
            return [
                'id' => $this->company_id,
                'company_name' => $rawName,
                'legal_business_name' => $rawName,
                'verification_photo_id' => null,
                'company_logo_url' => $this->company_logo_url,
                'address_line1' => $rawAddr,
                'physical_address' => $rawAddr,
            ];
        }

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
        static::creating(function ($payment) {
            if (empty($payment->unique_check_id)) {
                $payment->unique_check_id = static::generateUniqueCheckId();
            }
        });


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
