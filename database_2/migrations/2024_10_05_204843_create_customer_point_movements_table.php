<?php

use App\Enums\CustomerPointTypeEnum;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
use App\Models\CustomerPoint;
use App\Models\Loyalty;
use App\Models\LoyaltyRewardProduct;
use App\Models\SaleRefund;
use App\Models\SaleRefundDetail;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
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
        Schema::create('customer_point_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class);
            $table->foreignIdFor(CustomerPoint::class);
            $table->foreignIdFor(CustomerOrder::class)->nullable();
            $table->foreignIdFor(CustomerOrderDetail::class)->nullable();
            $table->foreignIdFor(SaleTransaction::class)->nullable();
            $table->foreignIdFor(SaleTransactionDetail::class)->nullable();
            $table->foreignIdFor(SaleRefund::class)->nullable();
            $table->foreignIdFor(SaleRefundDetail::class)->nullable();
            $table->foreignIdFor(Loyalty::class)->nullable();
            $table->foreignIdFor(LoyaltyRewardProduct::class)->nullable();

            $table->string('code')->unique();
            $table->integer("point")->default(0);
            $table->enum("type", CustomerPointTypeEnum::toArray());

            $table->integer("transaction_value")->nullable();
            $table->integer("miniminal_transaction_value")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_point_movements');
    }
};
