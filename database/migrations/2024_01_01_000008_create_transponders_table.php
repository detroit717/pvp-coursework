<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transponders', function (Blueprint $table) {
            $table->smallIncrements('id_transponder');
            $table->smallInteger('id_vehicle')->unsigned();
            $table->string('serial_number', 100)->unique();
            $table->string('status', 20)->default('активен');
            $table->foreign('id_vehicle')->references('id_vehicle')->on('vehicles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transponders');
    }
};
