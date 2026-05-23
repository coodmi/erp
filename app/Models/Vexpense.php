<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vexpense extends Model
{
    use HasFactory;

    protected $table = 'vexpense';
    protected $fillable = [
        'expense_name', 'expense_type', 'expense_amount', 'expense_date', 'remarks',
    ];
}
