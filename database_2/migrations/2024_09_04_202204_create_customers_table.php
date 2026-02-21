<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Location;
use App\Models\User;
use App\Models\CustomerCategory;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(CustomerCategory::class)->nullable();
            $table->foreignIdFor(Location::class);

            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number');
            $table->string('phone_number_country_code');
            $table->string('email')->unique()->nullable();

            $table->timestamp('last_visit_at')->nullable();

            $table->integer("last_spend_daily")->unsigned()->nullable();
            $table->integer("last_spend_weekly")->unsigned()->nullable();
            $table->integer("last_spend_monthly")->unsigned()->nullable();

            $table->bigInteger("last_visit_location_id")->unsigned()->nullable();
            $table->foreign("last_visit_location_id")->references("id")->on("locations");

            $table->enum('status', ['active', 'archived']);

            $table->softDeletesTz('deleted_at', precision: 0);
            $table->timestamps();

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->unique(['phone_number', 'phone_number_country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
