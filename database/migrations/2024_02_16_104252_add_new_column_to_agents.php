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
        Schema::table('agents', function (Blueprint $table) {
            $table->string('unique_code')->nullable()->unique();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('refund', 10, 2)->default(0);
            $table->string('attachment2')->nullable();
            $table->string('attachmen3')->nullable();
        });

        // Generate and update unique codes for existing rows
        \App\Models\Agent::all()->each(function ($agent) {
            $agent->update(['unique_code' => 'AGT' . str_pad($agent->id, 6, '0', STR_PAD_LEFT)]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agents', function (Blueprint $table) {
            // $table->dropColumn('unique_code');
        });
    }
};

