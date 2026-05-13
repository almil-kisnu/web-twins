<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'jadwal_id',
        'store_id',
        'tanggal_absensi',
        'waktu_check_in',
        'status_kehadiran',
        'is_recent_sync',
    ];

    protected $casts = [
        'tanggal_absensi' => 'date',
        'is_recent_sync' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }
}
