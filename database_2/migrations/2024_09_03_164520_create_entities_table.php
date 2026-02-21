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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('initial')->unique();
            $table->string('name')->unique();
            $table->longText('image_url')->nullable();
            $table->longText('icon_image_url')->nullable();
            $table->string('phone_number');
            $table->string('phone_number_country_code');
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->longText('full_address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('province');
            $table->string('country');
            $table->string('timezone');
            $table->enum('status', ['active', 'archived']);
            $table->softDeletesTz('deleted_at', precision: 0);
            $table->timestamps();

            $table->unique(['phone_number', 'phone_number_country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
