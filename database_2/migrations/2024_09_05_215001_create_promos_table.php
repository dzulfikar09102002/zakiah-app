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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->bigInteger("owner_location_id")->unsigned();
            $table->foreign("owner_location_id")->references("id")->on("locations");

            $table->string('code')->unique();
            $table->longText('name');
            $table->longText('description')->nullable();
            $table->enum('channel', ['pos']);

            $table->timestamp('start_at', precision: 6);
            $table->timestamp('end_at', precision: 6)->nullable();

            $table->enum('goal', ['increase_sales']);
            $table->enum('status', ['scheduled', 'active', 'complete', 'cancelled', 'archived']);

            $table->boolean('auto_apply')->default(false);
            $table->boolean('combine_promo')->default(false);
            $table->boolean('free_of_charge')->default(false);
            $table->boolean('select_all_location')->default(true);

            $table->timestamps();
            $table->softDeletesTz('deleted_at', precision: 0);
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
        Schema::dropIfExists('promos');
    }
};
