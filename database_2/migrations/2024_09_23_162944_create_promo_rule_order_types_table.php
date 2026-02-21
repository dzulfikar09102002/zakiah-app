<?php

use App\Models\OrderType;
use App\Models\Promo;
use App\Models\PromoRule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promo_rule_order_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Promo::class);
            $table->foreignIdFor(PromoRule::class);
            $table->foreignIdFor(OrderType::class);

            $table->longText('order_type_name');

            $table->softDeletesTz('deleted_at', precision: 0);
            $table->bigInteger("updated_by")->unsigned()->nullable();
            $table->foreign("updated_by")->references("id")->on("users");

            $table->bigInteger("created_by")->unsigned()->nullable();
            $table->foreign("created_by")->references("id")->on("users");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_rule_order_types');
    }
};
