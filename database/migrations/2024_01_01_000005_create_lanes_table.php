<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lanes', function (Blueprint $table) {
            $table->smallIncrements('id_lane');
            $table->smallInteger('id_point')->unsigned();
            $table->integer('lane_number');
            $table->string('lane_type', 50)->default('Универсальный');
            $table->decimal('lane_price', 10, 2)->nullable();
            $table->string('lane_status', 20)->default('активна');
            $table->foreign('id_point')->references('id_point')->on('payment_points')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lanes');
    }
};
