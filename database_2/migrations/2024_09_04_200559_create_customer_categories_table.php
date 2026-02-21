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
        Schema::create('customer_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->longText('name');
            $table->enum('status', ['active', 'archived']);

            $table->boolean('required')->default(false);
            $table->timestamp('last_reset_at')->nullable();
            $table->enum('reset_every', ['daily', 'weekly', 'monthly', 'annual'])->nullable();

            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->timestamps();
            $table->softDeletesTz('deleted_at', precision: 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_categories');
    }
};
