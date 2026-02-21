<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Entity;
use App\Models\Employee;
// use App\Models\Supplier;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_transfer_services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Entity::class);
            // $table->foreignIdFor(Supplier::class)->nullable();
            $table->bigInteger("from_location_id")->unsigned()->nullable();
            $table->foreign("from_location_id")->references("id")->on("locations");

            $table->bigInteger("to_location_id")->unsigned()->nullable();
            $table->foreign("to_location_id")->references("id")->on("locations");

            $table->bigInteger("employee_requested_by")->unsigned()->nullable();
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
            $table->longText("rejected_reason")->nullable();
            $table->longText("rejected_note")->nullable();

            $table->bigInteger("employee_cancelled_by")->unsigned()->nullable();
            $table->foreign("employee_cancelled_by")->references("id")->on("employees");
            $table->timestamp("cancelled_at")->nullable();
            $table->timestamp("local_cancelled_at")->nullable();
            $table->longText("cancelled_reason")->nullable();
            $table->longText("cancelled_note")->nullable();

            $table->string("code");
            $table->longText("supplier_name")->nullable();

            $table->enum("status", ['requested', 'approved', 'rejected', 'cancelled']);
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
        Schema::dropIfExists('product_transfer_services');
    }
};
