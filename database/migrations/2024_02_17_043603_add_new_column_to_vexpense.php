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
        Schema::table('vexpense', function (Blueprint $table) {
            $table->unsignedBigInteger('vexcat_id')->nullable();
            $table->foreign('vexcat_id')->references('id')->on('vexcat')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vexpense', function (Blueprint $table) {
            //
        });
    }
};
