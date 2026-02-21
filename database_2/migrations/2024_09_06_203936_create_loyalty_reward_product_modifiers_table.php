<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Loyalty;
use App\Models\LoyaltyRewardProduct;
use App\Models\Modifier;
use App\Models\ModifierOption;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loyalty_reward_product_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Loyalty::class);
            $table->foreignIdFor(LoyaltyRewardProduct::class);
            $table->foreignIdFor(Modifier::class);
            $table->foreignIdFor(ModifierOption::class);

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
        Schema::dropIfExists('loyalty_reward_product_modifiers');
    }
};
