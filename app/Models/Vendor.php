<?php

// namespace App;
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    // Define the table associated with the model
    protected $table = 'vendors';

    // Fillable fields for mass assignment
    protected $fillable = ['vendor_name', 'visa_type', 'company_details',  'amount_paid', 'amount_due', 'unit_price', 'refund', 'visa_country_id', 'attachment', 'attachment2', 'attachmen3', 'attachment4'];


    // Guarded fields to prevent mass assignment
    // protected $guarded = [];

    // Cast attributes to appropriate types
    protected $casts = [
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    // Relationships
    // Example: a vendor may have many clients
    public function clients()
    {
        return $this->hasMany(VClient::class);
    }
}
