<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Loyalty;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loyalty_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Loyalty::class);
            $table->foreignIdFor(Location::class);

            $table->enum("kind", ['include', 'exclude']);
            
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
        Schema::dropIfExists('loyalty_locations');
    }
};
