<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\ProductCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->bigInteger("parent_id")->unsigned()->nullable();
            $table->foreign("parent_id")->references("id")->on("product_categories");

            $table->longText('name');
            $table->string('search_name');
            $table->enum('status', ['active', 'archived']);

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->timestamps();
            $table->softDeletesTz('deleted_at', precision: 0);

            $table->unique(['entity_id', 'search_name', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
