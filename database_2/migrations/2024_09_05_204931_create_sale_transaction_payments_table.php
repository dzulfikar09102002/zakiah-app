<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SaleTransaction;
use App\Models\PaymentMethod;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_transaction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(SaleTransaction::class);
            $table->foreignIdFor(PaymentMethod::class);
            $table->longText("payment_method_name");

            $table->integer("amount_receive")->unsigned();
            $table->integer("change")->unsigned();
            $table->integer("subsidize")->unsigned();
            $table->integer("platform_fee")->unsigned();

            $table->boolean("already_change_payment_method")->default(false);

            $table->longText("card_number")->nullable();
            $table->longText("approval_code")->nullable();

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
        Schema::dropIfExists('sale_transaction_payments');
    }
};
