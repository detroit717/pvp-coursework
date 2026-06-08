<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('auto_types')->insert([
            ['name' => 'Легковой'],
            ['name' => 'Грузовой'],
            ['name' => 'Автобус'],
            ['name' => 'Мотоцикл'],
        ]);

        DB::table('payment_methods')->insert([
            ['name' => 'Наличные'],
            ['name' => 'Банковская карта'],
            ['name' => 'Транспондер'],
        ]);

        DB::table('fine_types')->insert([
            ['name' => 'Неоплата проезда'],
            ['name' => 'Превышение скорости'],
            ['name' => 'Нарушение весовых норм'],
            ['name' => 'Прочие нарушения'],
        ]);
    }
}
