<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable implements MustVerifyEmail
{
    
    use HasFactory, Notifiable, HasUuids;


    protected $primaryKey = 'uuid';
    protected $keyType = 'string';


    protected $fillable = [
        'uuid',
        'username',
        'no_hp', 
        'email', 
        'password', 
        'operator_id', 
        'store_id',   
        'status_aktif',
        'last_login_at'
    ];

    protected $hidden = [
        'password', 
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status_aktif' => 'boolean',
        'last_login_at' => 'datetime'
    ];

    public function getNameAttribute()
    {
        return $this->username;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['username'] = $value;
    }

    public function getRoleAttribute()
    {
        if ($this->isOwner()) {
            return 'owner';
        }
        if ($this->operator) {
            return str_replace(' ', '_', strtolower(trim($this->operator->nama)));
        }
        return 'user';
    }

    public function setRoleAttribute($value)
    {
        // Convert 'owner' or 'Owner' or 'kepala_toko' back to DB format
        $nama = str_replace('_', ' ', ucwords($value, '_'));
        $operator = Operator::where('nama', $nama)->first();
        if ($operator) {
            $this->attributes['operator_id'] = $operator->uuid;
        }
    }

    public function getOutletIdAttribute()
    {
        return $this->store_id;
    }

    public function setOutletIdAttribute($value)
    {
        $this->attributes['store_id'] = $value;
    }

    public function getAuthIdentifierName()
    {
        return 'uuid';
    }


    public function isOwner(): bool
    {
        // Trim dan cast ke string untuk menangani spasi atau data non-null tapi kosong
        return empty(trim((string)$this->operator_id));
    }

    /**
     * Check if user can access administrative area
     */
    public function canAccessAdmin(): bool
    {
        // Hanya Owner (operator_id null) yang boleh akses area admin/dashboard
        return $this->isOwner();
    }

    public function hasFeature($featureId): bool
    {
        if ($this->isOwner()) {
            return true;
        }
        return $this->operator ? $this->operator->hasFeature($featureId) : false;
    }

    public function isKepalaToko(): bool
    {
        return in_array($this->role, ['Kepala Toko', 'kepala_toko']);
    }


    public function operator()
    {
        return $this->belongsTo(Operator::class, 'operator_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function outlet()
    {
        return $this->store();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StoreReview::class, 'user_id', 'uuid');
    }

}
