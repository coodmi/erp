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
        Schema::create('vexpense', function (Blueprint $table) {
            $table->id();
            $table->string('expense_name')->nullable();
            $table->string('expense_type')->nullable();
            $table->decimal('expense_amount', 10, 2)->default(0);
            $table->date('expense_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vexpense');
    }
};
