<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Loyalty;
use App\Models\Product;
use App\Models\ProductUnit;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loyalty_reward_products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Loyalty::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductUnit::class);

            $table->integer("point_needed")->unsigned();
            $table->integer("maximum_quantity")->unsigned()->nullable();

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
        Schema::dropIfExists('loyalty_reward_products');
    }
};
