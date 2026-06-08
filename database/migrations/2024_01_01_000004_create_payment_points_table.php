<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_points', function (Blueprint $table) {
            $table->smallIncrements('id_point');
            $table->string('name', 255);
            $table->string('location', 255)->nullable();
            $table->integer('lanes_count')->default(1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_points');
    }
};
