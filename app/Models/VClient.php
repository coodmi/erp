<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VClient extends Model
{
    // Define the table associated with the model
    protected $table = 'clients';

    // Fillable fields for mass assignment
    protected $fillable = [
        'client_name', 'passport_no', 'visa_type', 'amount_paid', 'amount_due', 'isTicket', 'status', 'agent_id', 'vendor_id', 'unit_price', 'refund', 'visa_country_id', 'attachment', 'attachment2', 'attachmen3', 'attachment4' // Add more fields as needed
    ];

    // Guarded fields to prevent mass assignment
    // protected $guarded = [];

    // Cast attributes to appropriate types
    protected $casts = [
        'isTicket' => 'boolean', // Cast 'isTicket' attribute to boolean
    ];

    // Relationships
    // Example: a VClient belongs to an agent
    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    // Example: a VClient belongs to a vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
