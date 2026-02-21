<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Taking;
use App\Models\Tax;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('taking_taking_details', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Taking::class);
            $table->foreignIdFor(Tax::class);

            $table->string("tax_name");

            $table->integer("sales_amount");
            $table->integer("sales_count");

            $table->integer("refund_amount");
            $table->integer("refund_count");

            $table->integer("total_amount");

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
        Schema::dropIfExists('taking_taking_details');
    }
};
