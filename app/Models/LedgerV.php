<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerV extends Model
{
    use HasFactory;

    protected $table = 'vledger';

    protected $fillable = [
        'vendor_id',
        'date',
        'paid_for',
        'unit_pirce',
        'number_of_unit',
        'payment_mode',
        'amount',
        'advance',
        'due',
        'refund',
        'deposit'
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
        
    }
}
