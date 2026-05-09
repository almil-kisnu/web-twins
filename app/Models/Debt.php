<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Debt extends Model
{
    use HasFactory;

    protected $table = 'debts';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'store_id', 'kontak_id', 'tipe', 'nominal', 'sisa', 'jatuh_tempo', 'keterangan', 'reference_id', 'reference_type'
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

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'kontak_id', 'uuid');
    }
    
    public function detailDebts()
    {
        return $this->hasMany(DetailDebt::class, 'debts_id', 'uuid');
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
