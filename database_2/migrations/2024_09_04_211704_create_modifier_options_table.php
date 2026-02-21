<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Modifier;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modifier_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Modifier::class);
            $table->morphs('option'); # can be product or modifier

            $table->integer("quantity")->unsigned();
            $table->integer("sell_price")->unsigned();
            $table->smallInteger("sequence")->unsigned();

            $table->boolean("pre_selected")->default(false);
            $table->integer("pre_selected_quantity")->unsigned();
            $table->boolean("product_with_variance")->default(false);
            $table->boolean("product_with_modifier")->default(false);
            
            $table->enum('status', ['active', 'archived']);

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->softDeletesTz('deleted_at', precision: 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_options');
    }
};
