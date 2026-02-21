<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('location_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class);
            $table->enum('kind', ['shift', 'open_hour']);
            $table->enum('name_of_day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->boolean('always_open')->default(false);

            $table->tinyInteger('start_hour');
            $table->tinyInteger('end_hour');

            $table->timestamps();

            $table->unique(['kind', 'name_of_day', 'location_id']);

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
        Schema::dropIfExists('location_hours');
    }
};
