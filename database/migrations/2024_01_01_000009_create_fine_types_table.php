<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fine_types', function (Blueprint $table) {
            $table->smallIncrements('id_fine_type');
            $table->string('name', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_types');
    }
};
