<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->smallIncrements('id_vehicle');
            $table->smallInteger('id_auto_type')->unsigned();
            $table->smallInteger('id_driver')->unsigned();
            $table->string('plate_number', 20)->unique();
            $table->string('name', 255)->nullable();
            $table->foreign('id_auto_type')->references('id_auto_type')->on('auto_types')->onUpdate('cascade');
            $table->foreign('id_driver')->references('id_driver')->on('drivers')->onDelete('cascade');
            $table->index('plate_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
