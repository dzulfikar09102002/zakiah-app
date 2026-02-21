<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Location;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('location_printers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class);
            $table->string('printer_type');
            $table->string('printer_name');
            $table->string('printer_vendor');
            $table->json('printer_setting');

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
        Schema::dropIfExists('location_printers');
    }
};
