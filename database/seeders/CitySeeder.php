<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cityJson = file_get_contents(database_path('data/city.json'));

        $citys = json_decode($cityJson, true);

        foreach ($citys as $city) {
            City::create([
                'name' => $city['name'],
                'state_id' => $city['state_id'],
            ]);
        }
    }
}
