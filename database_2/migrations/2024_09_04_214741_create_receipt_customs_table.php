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
        Schema::create('receipt_customs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Location::class);

            $table->enum("kind", ['receipt', 'docket', 'pre-settlement']);
            $table->enum("reset_counter_on", ['never', 'end_of_day']);

            $table->boolean("using_sequence")->default(true);
            $table->boolean("show_location_initial")->default(true);

            $table->boolean("using_date")->default(true);
            $table->enum("date_format", ['dd-mm-yyyy', 'dd/mm/yyyy', 'yyyy-mm-dd', 'yymmdd']);

            $table->boolean("using_time")->default(true);
            $table->enum("time_format", ['hh:mm', 'hh:mm:ss']);

            $table->smallInteger("zero_holder_size")->default(7);
            $table->string("location_initial");
            $table->string("custom_text")->default('INV');

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
        Schema::dropIfExists('receipt_customs');
    }
};
