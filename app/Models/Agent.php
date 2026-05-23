<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Agent extends Model
{

    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($agent) {
            $agent->unique_code = 'AGT' . str_pad($agent->id, 6, '0', STR_PAD_LEFT);
        });
    }
    // Define the table associated with the model
    protected $table = 'agents';

    // Fillable fields for mass assignment
    protected $fillable = [
        'agent_name', 'passport_number', 'visa_type', 'amount_paid', 'amount_due', 'unit_price', 'refund', 'visa_country_id', 'attachment', 'attachment2', 'attachmen3', 'attachment4'    ];

    // Guarded fields to prevent mass assignment
    // protected $guarded = [];

    // Relationships
    // Example: an agent may have many clients
    public function clients()
    {
        return $this->hasMany(VClient::class);
    }
}
