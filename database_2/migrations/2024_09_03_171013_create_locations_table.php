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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);

            $table->string('code')->unique();
            $table->string('initial');
            $table->longText('name');
            $table->longText('search_name');
            $table->longText('image_url')->nullable();
            $table->longText('icon_image_url')->nullable();

            $table->string('backoffice_phone_number')->nullable();
            $table->string('backoffice_phone_number_country_code')->nullable();
            $table->longText('backoffice_email')->nullable();

            $table->string('contact_phone_number')->nullable();
            $table->string('contact_phone_number_country_code')->nullable();
            $table->longText('contact_email')->nullable();

            $table->enum('kind', ['main_office', 'outlet', 'warehouse']);
            $table->boolean('warehouse')->default(false);

            $table->longText('full_address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('country')->nullable();
            $table->string('timezone')->nullable();
            $table->longText('footer')->nullable();

            $table->boolean('allow_transfer_stock')->default(true);
            $table->boolean('allow_external_supplier')->default(true);
            $table->boolean('franchise')->default(false);
            $table->enum('status', ['active', 'archived']);

            $table->softDeletesTz('deleted_at', precision: 0);
            $table->timestamps();

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->char('checksum', 32);

            $table->unique(['entity_id', 'checksum']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
