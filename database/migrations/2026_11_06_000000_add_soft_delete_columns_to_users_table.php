<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sama dengan database/sql/2026_11_06_add_soft_delete_to_users.sql. Di server, kolom
 * dibuat lewat SQL manual; migration ini hanya menambah bila belum ada (tes, install baru).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'deleted_email')) {
                $table->string('deleted_email')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        // Sengaja kosong: kolom di server dikelola lewat SQL manual.
    }
};
