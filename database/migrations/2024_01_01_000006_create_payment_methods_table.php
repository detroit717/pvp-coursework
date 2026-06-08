<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->smallIncrements('id_payment_method');
            $table->string('name', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
