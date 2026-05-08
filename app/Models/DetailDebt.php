<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DetailDebt extends Model
{
    use HasFactory;

    protected $table = 'detail_debts';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'debts_id', 'sebelum', 'bayar', 'sisa', 'metode_pembayaran', 'tanggal', 'user_id'
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'metode_pembayaran', 'uuid');
    }
}
