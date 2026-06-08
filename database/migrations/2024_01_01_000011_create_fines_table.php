<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fines', function (Blueprint $table) {
            $table->increments('id_fine');
            $table->smallInteger('id_driver')->unsigned();
            $table->smallInteger('id_vehicle')->unsigned()->nullable();
            $table->integer('id_transaction')->unsigned()->nullable();
            $table->smallInteger('id_point')->unsigned()->nullable();
            $table->smallInteger('id_fine_type')->unsigned();
            $table->decimal('amount', 10, 2);
            $table->timestamp('datetime')->useCurrent();
            $table->string('payment_status', 20)->default('неоплачен');
            $table->text('comment')->nullable();
            $table->foreign('id_driver')->references('id_driver')->on('drivers')->onDelete('cascade');
            $table->foreign('id_vehicle')->references('id_vehicle')->on('vehicles')->onDelete('set null');
            $table->foreign('id_transaction')->references('id_transaction')->on('transactions')->onDelete('set null');
            $table->foreign('id_fine_type')->references('id_fine_type')->on('fine_types');
            $table->index('id_driver');
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fines');
    }
};
