<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->smallIncrements('id_driver');
            $table->string('full_name', 255);
            $table->string('phone_number', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->decimal('personal_balance', 10, 2)->default(0.00);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
