<?php

use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Config::count() == 0) {
            Config::create([
                'idpar' => 2,
                'endpoint' => 'Connectors',
                'nombre' => 'Bezier',
                'nombre_visible' => 'Curvo',
                'cuenta_id' => 0
            ]);
            Config::create([
                'idpar' => 2,
                'endpoint' => 'Connectors',
                'nombre' => 'Straight',
                'nombre_visible' => 'Recto',
                'cuenta_id' => 0
            ]);
            Config::create([
                'idpar' => 2,
                'endpoint' => 'Connectors',
                'nombre' => 'Flowchart',
                'nombre_visible' => 'Diagrama de flujo',
                'cuenta_id' => 0
            ]);
            Config::create([
                'idpar' => 2,
                'endpoint' => 'Connectors',
                'nombre' => 'StateMachine',
                'nombre_visible' => 'Curvo Ligero',
                'cuenta_id' => 0
            ]);
        }
    }
}
