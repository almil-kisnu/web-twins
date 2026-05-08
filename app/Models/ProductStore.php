<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductStore extends Model
{
    use HasUuids;
    
    protected static function booted()
    {
        static::updated(function ($ps) {
            // Only sync if 'stok' was changed manually (not via Opname finalization itself)
            // To prevent recursion, we check if we're currently in a finalization process? 
            // Actually, finalizeOpname updates status to 'Selesai' AFTER updating stok.
            
            if ($ps->wasChanged('stok')) {
                $diff = $ps->stok - $ps->getOriginal('stok');
                
                // Find all PENDING opname details for this product and store
                $pendingDetails = \App\Models\OpnameDetail::where('product_id', $ps->product_id)
                    ->whereHas('opname', function($q) use ($ps) {
                        $q->where('store_id', $ps->store_id)->where('status', 'Pending');
                    })->get();
                
                foreach ($pendingDetails as $detail) {
                    // Always update stok_sistem to match current reality
                    $detail->stok_sistem = $ps->stok;
                    
                    // If user already entered physical count, adjust it proportionally
                    if ($detail->stok_fisik !== null) {
                        $detail->stok_fisik += $diff;
                    }
                    
                    // Recalculate selisih
                    if ($detail->stok_fisik !== null) {
                        $detail->selisih = $detail->stok_fisik - $detail->stok_sistem;
                    } else {
                        $detail->selisih = 0;
                    }
                    
                    $detail->save();
                }
            }
        });
    }

    protected $table = 'product_store';
    protected $primaryKey = 'uuid';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'product_id',
        'store_id',
        'stok',
        'kadaluarsa',
        'status_aktif',
        'stok_minimum',
        'tanggal_masuk'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'uuid');
    }

    public function store()
    {
        return $this->belongsTo(Outlet::class, 'store_id', 'uuid');
    }
}
