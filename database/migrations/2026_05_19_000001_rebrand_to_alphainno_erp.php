<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $erpPatterns = ['%ERPGo%', '%ERPGO%', '%ERP GO%', '%erpgo%'];

        foreach (['title_text', 'mail_from_name'] as $name) {
            $query = DB::table('settings')->where('name', $name);
            $query->where(function ($q) use ($erpPatterns) {
                foreach ($erpPatterns as $pattern) {
                    $q->orWhere('value', 'like', $pattern);
                }
                $q->orWhere('value', '');
            });
            $query->update(['value' => 'Alphainno ERP']);
        }

        DB::table('settings')->where('name', 'footer_text')->where(function ($q) use ($erpPatterns) {
            foreach ($erpPatterns as $pattern) {
                $q->orWhere('value', 'like', $pattern);
            }
            $q->orWhere('value', '');
        })->update(['value' => 'Alphainno ERP - Visa Consultancy Management System']);

        DB::table('settings')->where('name', 'company_logo_dark')->update(['value' => 'logo-dark.png']);
        DB::table('settings')->where('name', 'company_logo_light')->update(['value' => 'logo-light.png']);
        DB::table('settings')->where('name', 'company_favicon')->update(['value' => 'favicon.png']);
    }

    public function down(): void
    {
        // Branding rollback is not automated.
    }
};
