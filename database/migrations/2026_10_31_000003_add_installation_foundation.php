<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 20)->default('user');
        });
        Schema::create('installation_receipts', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->uuid('attempt')->unique();
            $table->foreignId('admin_id')->constrained('users');
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installation_receipts');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
