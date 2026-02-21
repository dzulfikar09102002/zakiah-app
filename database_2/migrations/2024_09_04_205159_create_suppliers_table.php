<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);

            $table->string('code')->unique();
            $table->string('initial')->unique();
            $table->longText('name');

            $table->string('contact_phone_number')->nullable();
            $table->string('contact_phone_number_country_code')->nullable();
            $table->longText('contact_email')->nullable();

            $table->longText('full_address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('province');
            $table->string('country');
            
            $table->enum('status', ['active', 'archived']);

            $table->softDeletesTz('deleted_at', precision: 0);
            $table->timestamps();

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
