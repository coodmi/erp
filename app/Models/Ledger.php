<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $table = 'ledger';

    protected $fillable = [
        'agent_id',
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

    public function agent()
    {
        return $this->belongsTo(Agent::class);
        
    }


}
