<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // firma_id nullable machen für Developer
            $table->unsignedBigInteger('firma_id')->nullable()->change();
        });
        
        // Bestehende Developer von Firma-Zuordnung befreien
        DB::table('users')
            ->where('role', 'developer')
            ->update(['firma_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('firma_id')->nullable(false)->change();
        });
    }
};
