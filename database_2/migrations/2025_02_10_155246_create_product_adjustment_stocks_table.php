<?php

use App\Models\Entity;
use App\Models\Location;
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
        Schema::create('product_adjustment_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            $table->foreignIdFor(Location::class);
            
            $table->bigInteger("employee_requested_by")->unsigned();
            $table->foreign("employee_requested_by")->references("id")->on("employees");
            $table->timestamp("requested_at")->nullable();
            $table->timestamp("local_requested_at")->nullable();
            $table->longText("request_note")->nullable();

            $table->bigInteger("employee_approved_by")->unsigned()->nullable();
            $table->foreign("employee_approved_by")->references("id")->on("employees");
            $table->timestamp("approved_at")->nullable();
            $table->timestamp("local_approved_at")->nullable();
            $table->longText("approval_note")->nullable();

            $table->bigInteger("employee_rejected_by")->unsigned()->nullable();
            $table->foreign("employee_rejected_by")->references("id")->on("employees");
            $table->timestamp("rejected_at")->nullable();
            $table->timestamp("local_rejected_at")->nullable();
            $table->longText("rejected_note")->nullable();

            $table->string("code");
            $table->enum('status', ['requested', 'approved', 'rejected']);

            $table->integer("recorded_product_count")->unsigned();
            $table->integer("counted_product_count")->unsigned();
            $table->integer("difference_product_count")->unsigned();

            $table->integer("recorded_stock");
            $table->integer("counted_stock");
            $table->integer("difference_stock");

            $table->longText("note")->nullable();
            $table->boolean("auto_approve")->default(true);

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
        Schema::dropIfExists('product_adjustment_stocks');
    }
};
