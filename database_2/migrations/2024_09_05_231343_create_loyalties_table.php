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
        Schema::create('loyalties', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);

            $table->string("code");
            $table->longText("name");
            $table->longText("description")->nullable();

            $table->integer("miniminal_transaction_value");
            $table->integer("reward_point");
            $table->integer("conversion_point")->nullable();
            $table->integer("conversion_amount")->nullable();

            $table->boolean("allow_multiple")->default(true);
            $table->boolean("include_discount_and_promo")->default(true);
            $table->boolean("include_surcharge")->default(true);
            $table->boolean("include_free_of_charge")->default(false);
            $table->boolean("include_tax")->default(true);
            $table->boolean("include_service_charge")->default(true);
            $table->boolean("select_all_location")->default(true);
            $table->boolean("allow_convert_point_as_amount")->default(false);
            $table->boolean("purchase_required")->default(false);

            $table->enum('status', ['active', 'in_active', 'archived']);

            $table->timestamps();
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->unique(['entity_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalties');
    }
};
