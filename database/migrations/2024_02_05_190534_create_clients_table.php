<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id(); // This creates the 'id' column as the auto-incrementing primary key
            $table->string('client_name')->notNullable();
            $table->string('passport_no')->nullable();
            $table->string('visa_type')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('amount_due', 10, 2)->default(0);
            $table->boolean('isTicket')->default(0);
            $table->string('status')->default('submitted');
            $table->string('attachment')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable(); // Add vendor foreign key
            $table->timestamps();
        
            // Define foreign key constraints
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
