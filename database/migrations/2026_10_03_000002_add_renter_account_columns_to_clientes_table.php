<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->string('nome', 120)->change();
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->restrictOnDelete();
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropUnique(['email']);
            $table->dropColumn(['email', 'phone']);
            $table->string('nome', 30)->change();
        });
    }
};
