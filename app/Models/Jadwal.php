<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['uuid', 'user_id', 'shift_id', 'store_id', 'hari_dalam_minggu'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class, 'shift_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'jadwal_id', 'uuid');
    }

    /**
     * Helper: Get hari name from integer
     */
    public static function hariName(int $hari): string
    {
        $names = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $names[$hari] ?? '-';
    }
}
