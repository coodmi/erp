<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vexcat extends Model
{
    use HasFactory;

    protected $table = 'vexcat';

    protected $fillable = [
        'category_name',
        'category_description',
    ];

    public function vexpenses()
    {
        return $this->hasMany(Vexpense::class);
    }

    public function vexpense()
    {
        return $this->belongsTo(Vexpense::class);
    }
    
}
