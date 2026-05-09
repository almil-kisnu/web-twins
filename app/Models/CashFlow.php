<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CashFlow extends Model
{
    use HasFactory;

    protected $table = 'cash_flows';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'store_id', 'user_id', 'jenis', 'nominal', 'keterangan', 'tanggal', 'metode_pembayaran'
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

    public function outlet()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'metode_pembayaran', 'uuid');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'reference_id', 'uuid');
    }

    public function paymentOrder()
    {
        return $this->belongsTo(PaymentOrder::class, 'reference_id', 'uuid');
    }
}
