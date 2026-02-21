<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PaymentMethod;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_method_custom_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PaymentMethod::class);
            $table->foreignIdFor(Location::class);
            $table->integer('fixed_fee')->unsigned();
            $table->integer('variable_fee')->unsigned();

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
        Schema::dropIfExists('payment_method_custom_fees');
    }
};
