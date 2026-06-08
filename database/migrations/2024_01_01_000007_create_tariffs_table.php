<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->smallIncrements('id_tariff');
            $table->smallInteger('id_auto_type')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->time('time_start')->default('00:00');
            $table->time('time_end')->default('23:59');
            $table->tinyInteger('day_of_week')->nullable();
            $table->foreign('id_auto_type')->references('id_auto_type')->on('auto_types')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
