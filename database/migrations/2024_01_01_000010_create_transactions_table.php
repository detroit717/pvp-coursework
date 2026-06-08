<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id_transaction');
            $table->smallInteger('id_point')->unsigned();
            $table->smallInteger('id_lane')->unsigned();
            $table->smallInteger('id_vehicle')->unsigned();
            $table->smallInteger('id_tariff')->unsigned()->nullable();
            $table->decimal('amount', 10, 2);
            $table->smallInteger('id_payment_method')->unsigned();
            $table->smallInteger('id_transponder')->unsigned()->nullable();
            $table->string('status', 20)->default('успешно');
            $table->timestamp('datetime')->useCurrent();
            $table->foreign('id_point')->references('id_point')->on('payment_points');
            $table->foreign('id_lane')->references('id_lane')->on('lanes');
            $table->foreign('id_vehicle')->references('id_vehicle')->on('vehicles');
            $table->foreign('id_tariff')->references('id_tariff')->on('tariffs');
            $table->foreign('id_payment_method')->references('id_payment_method')->on('payment_methods');
            $table->foreign('id_transponder')->references('id_transponder')->on('transponders');
            $table->index('datetime');
            $table->index('id_point');
            $table->index('id_vehicle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
