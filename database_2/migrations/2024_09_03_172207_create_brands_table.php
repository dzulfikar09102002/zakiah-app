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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);

            $table->string('code')->unique();
            $table->string('initial')->unique();
            $table->longText('name');
            $table->longText('image_url')->nullable();
            $table->longText('icon_image_url')->nullable();

            $table->enum('status', ['active', 'archived']);

            $table->softDeletesTz('deleted_at', precision: 0);
            $table->timestamps();

            $table->bigInteger("updated_by")->unsigned()->nullable();

            $table->bigInteger("created_by")->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
