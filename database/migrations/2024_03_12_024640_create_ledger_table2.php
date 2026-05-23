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
        Schema::create('ledger', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->date('date')->nullable();
            $table->string('paid_for')->nullable();
            $table->float('unit_pirce', 10, 2)->default(0);
            $table->integer('number_of_unit')->default(1);
            $table->string('payment_mode')->nullable();
            $table->float('amount', 10, 2)->default(0);
            $table->float('advance', 10, 2)->default(0);
            $table->float('due', 10, 2)->default(0);
            $table->float('refund', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger');
    }
};
