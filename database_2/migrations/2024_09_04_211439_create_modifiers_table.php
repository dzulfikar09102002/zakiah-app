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
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);

            $table->longText('name');
            $table->integer("min_quantity")->unsigned();
            $table->integer("max_quantity")->unsigned();

            $table->boolean("show_instruction")->default(false);
            $table->boolean("include_price_to_parent")->default(true);
            $table->boolean("has_modifier_as_option")->default(false);
            $table->boolean("print_to_receipt")->default(true);
            
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
        Schema::dropIfExists('modifiers');
    }
};
