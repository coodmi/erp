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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('unique_code')->nullable()->unique();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('refund', 10, 2)->default(0);
            $table->string('attachment2')->nullable();
            $table->string('attachmen3')->nullable();
        });

        \App\Models\Vendor::all()->each(function ($vendor) {
            $vendor->update(['unique_code' => 'VDR' . str_pad($vendor->id, 6, '0', STR_PAD_LEFT)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            //
        });
    }
};
