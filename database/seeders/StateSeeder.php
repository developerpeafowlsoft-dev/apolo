<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stateJson = file_get_contents(database_path('data/states.json'));

        $states = json_decode($stateJson, true);

        foreach ($states as $state) {
            State::create([
                'name' => $state['name'],
                'country_id' => $state['country_id'],
            ]);
        }
    }
}
