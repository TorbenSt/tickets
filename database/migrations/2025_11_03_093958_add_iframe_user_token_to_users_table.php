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
        Schema::table('users', function (Blueprint $table) {
            $table->string('iframe_user_token', 64)->nullable()->unique()->after('email');
            $table->timestamp('iframe_token_created_at')->nullable()->after('iframe_user_token');
            $table->timestamp('iframe_token_last_used')->nullable()->after('iframe_token_created_at');
            
            $table->index('iframe_user_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['iframe_user_token']);
            $table->dropColumn(['iframe_user_token', 'iframe_token_created_at', 'iframe_token_last_used']);
        });
    }
};
