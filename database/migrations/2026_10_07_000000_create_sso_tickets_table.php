<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tiket SSO antara zakiah-app dan NewZakicaPOS (database bersama).
 * Tiket bisa dipakai berulang (user bolak-balik) selama sesi di aplikasi asal masih hidup:
 * berlaku sampai expires_at (= umur sesi aplikasi asal) dan dicabut (revoked_at) saat logout.
 * Yang disimpan hanya hash SHA-256 token. Waktu: DATETIME berisi UTC (UTC_TIMESTAMP()),
 * sengaja bukan TIMESTAMP agar tidak dikonversi timezone sesi yang bisa beda antar aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sso_tickets', function (Blueprint $table) {
            $table->id();
            $table->char('token_hash', 64)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('source', 32);
            $table->string('audience', 32);
            $table->string('issued_ip', 45)->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('revoked_at')->nullable();
            $table->dateTime('last_used_at')->nullable();
            $table->string('last_used_ip', 45)->nullable();
            $table->unsignedInteger('use_count')->default(0);
            $table->dateTime('created_at')->nullable();

            $table->index(['audience', 'expires_at']);
            $table->index(['user_id', 'source', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sso_tickets');
    }
};
