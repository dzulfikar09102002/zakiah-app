<?php

use App\Models\Device;
use App\Models\Entity;
use App\Models\Location;
use App\Models\Taking;
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
        Schema::create('daily_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Location::class);
            $table->foreignIdFor(Taking::class)->nullable();
            $table->foreignIdFor(Device::class);
            $table->bigInteger("checkpoint_device_id")->unsigned()->nullable();
            $table->foreign("checkpoint_device_id")->references("id")->on("devices");

            $table->timestamp('sales_at', precision: 6)->nullable();
            $table->timestamp('local_sales_at', precision: 6)->nullable();

            $table->integer("sales_amount");
            $table->integer("refund_amount");

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
        Schema::dropIfExists('daily_sales');
    }
};
