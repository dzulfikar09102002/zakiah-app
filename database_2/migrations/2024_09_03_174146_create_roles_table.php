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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class)->nullable();

            $table->bigInteger("parent_id")->unsigned()->nullable();
            $table->foreign("parent_id")->references("id")->on("roles");

            $table->longText('name');
            $table->tinyInteger('tier');
            $table->tinyInteger('level');
            $table->json('entity_permission');
            $table->json('location_permission');

            $table->boolean('allow_pos')->default(true);
            $table->boolean('allow_backoffice')->default(true);

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
        Schema::dropIfExists('roles');
    }
};
