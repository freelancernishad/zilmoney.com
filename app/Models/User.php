<?php

namespace App\Models;

use App\Models\Plan\PlanSubscription;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $with = ['personalInfo', 'businessDetails', 'deviceLogs'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'stripe_id',

        'is_active',
        'is_blocked',
        'role',
        'notes',
        'last_login_at',
        'two_factor_verification',
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'otp_expires_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'last_login_at' => 'datetime',
            'two_factor_verification' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }




     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => !is_null($this->email_verified_at),
            'is_active' => $this->is_active,
            'is_blocked' => $this->is_blocked,
            'role' => $this->role,
            'last_login_at' => optional($this->last_login_at)->toDateTimeString(),
            "guard" => "user",
            "model" => User::class
        ];
    }





    public function photos()
    {
        return $this->hasMany(UserPhoto::class);
    }

    public function primaryPhoto()
    {
        return $this->hasOne(UserPhoto::class)->where('is_primary', true);
    }

    public function personalInfo()
    {
        return $this->hasOne(\App\Models\Zilmoney\PersonalInfo::class);
    }

    public function businessDetails()
    {
        return $this->hasOne(\App\Models\Zilmoney\BusinessDetail::class); // Assuming single business for the TS interface
    }

    public function deviceLogs()
    {
        return $this->hasMany(\App\Models\Zilmoney\DeviceLog::class)->orderBy('created_at', 'desc')->take(10);
    }

    public function planSubscriptions()
    {
        return $this->hasMany(\App\Models\Plan\PlanSubscription::class);
    }

    protected $appends = [
        'profile_picture',
        'documents',
        'accounts',
        'payees',
    ];

    // Accessor for profile picture
    public function getProfilePictureAttribute()
    {
        return $this->primaryPhoto?->path;
    }

    // Accessor for documents (Aggregating from BusinessDetail)
    public function getDocumentsAttribute()
    {
        $business = $this->businessDetails;
        if (!$business) return null;
        
        $docs = $business->documents;
        
        return [
            'formation_document' => $docs->where('type', 'formation_document')->first()?->file_path,
            'ownership_document' => $docs->where('type', 'ownership_document')->first()?->file_path,
            'principal_officer_id' => $docs->where('type', 'principal_officer_id')->first()?->file_path,
            'supporting_documents' => $docs->whereNotIn('type', ['formation_document', 'ownership_document', 'principal_officer_id'])->map(function($doc) {
                return [
                    'type' => $doc->type,
                    'file' => $doc->file_path
                ];
            })->values(),
        ];
    }

    public function getAccountsAttribute()
    {
        return $this->businessDetails?->accounts ?? [];
    }

    public function getPayeesAttribute()
    {
        return $this->businessDetails?->payees ?? [];
    }


}
