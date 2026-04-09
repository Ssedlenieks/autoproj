<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportCsvSeeder extends Seeder {

    public function run(): void {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');


    DB::table('makes')->truncate();
    DB::table('models')->truncate();
    DB::table('cars')->truncate();
    DB::table('engines')->truncate();
    DB::table('car_engine')->truncate();
    DB::table('power_mods')->truncate();
    DB::table('power_mod_variants')->truncate();

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo "Importing makes...\n";
        $this->importMakes();

        echo "Importing models...\n";
        $this->importModels();

        echo "Importing cars...\n";
        $this->importCars();

        echo "Importing engines...\n";
        $this->importEngines();

        echo "Importing car_engine...\n";
        $this->importCarEngine();

        echo "Importing power_mods...\n";
        $this->importPowerMods();

        echo "Importing power_mod_variants...\n";
        $this->importPowerModVariants();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        echo " All CSV data imported!\n";
    }

    private function importMakes() {
        $file = fopen(storage_path('makes.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('makes')->insert([
                'id'         => (int)$row[0],
                'name'       => $row[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo "  Imported $count makes\n";
    }

    private function importModels() {
        $file = fopen(storage_path('models.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('models')->insert([
                'id'         => (int)$row[0],
                'make_id'    => (int)$row[1],
                'name'       => $row[2],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo " Imported $count models\n";
    }

    private function importCars() {
        $file = fopen(storage_path('cars.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('cars')->insert([
                'id'         => (int)$row[0],
                'model_id'   => (int)$row[1],
                'year'       => (int)$row[2],
                'trim'       => $row[3],
                'body_style' => $row[4],
                'drive_type' => $row[5],
                'weight_kg'  => !empty($row[6]) ? (int)$row[6] : null,
                'image_url' => $row[7] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo " Imported $count cars\n";
    }

private function importEngines() {
    $file = fopen(storage_path('engines.csv'), 'r');
    fgetcsv($file);

    $count = 0;
    while (($row = fgetcsv($file)) !== false) {
        if (empty($row[0])) continue;

        DB::table('engines')->insert([
            'id'           => (int)$row[0],
            'code'         => $row[1],
            'subvariant'   => $row[2] ?? null,
            'fuel_type'    => $row[3] ?? null,
            'cylinder'     => $row[4] ?? null,
            'enginecreatedat' => $row[5] ?? null,
            'engine_discontinued' => $row[6] ?? null,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        $count++;
    }
    fclose($file);
    echo " Imported $count engines\n";
}


    private function importCarEngine() {
        $file = fopen(storage_path('Car_engines.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('car_engine')->insert([
                'id'                 => (int)$row[0],
                'car_id'             => (int)$row[1],
                'engine_id'          => (int)$row[2],
                'power_hp'           => !empty($row[3]) ? (int)$row[3] : null,
                'torque_nm'          => !empty($row[4]) ? (int)$row[4] : null,
                'acceleration_0_100' => !empty($row[5]) ? (float)str_replace(',', '.', $row[5]) : null,
                'top_speed'          => !empty($row[6]) ? (int)$row[6] : null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo " Imported $count car_engine records\n";
    }

    private function importPowerMods() {
        $file = fopen(storage_path('powerparts_desc.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('power_mods')->insert([
                'id'          => (int)$row[0],
                'name'        => $row[1],
                'brand'       => $row[2] ?? null,
                'category'    => $row[3],
                'is_estimate'       => $row[4] ?? null,
                'notes' => !empty($row[5]) ? (bool)(int)$row[5] : true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo " Imported $count power_mods\n";
    }

    private function importPowerModVariants() {
        $file = fopen(storage_path('powermods_to_car.csv'), 'r');
        fgetcsv($file);

        $count = 0;
        while (($row = fgetcsv($file)) !== false) {
            if (empty($row[0])) continue;

            DB::table('power_mod_variants')->insert([
                'id'              => (int)$row[0],
                'power_mod_id'    => (int)$row[1],
                'car_id'          => (int)$row[2],
                'engine_id'       => (int)$row[3],
                'hp_gain'         => !empty($row[4]) ? (int)$row[4] : null,
                'torque_nm_gain'  => !empty($row[5]) ? (int)$row[5] : null,
                'notes'           => $row[6] ?? null,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $count++;
        }
        fclose($file);
        echo " Imported $count power_mod_variants\n";
    }
}
