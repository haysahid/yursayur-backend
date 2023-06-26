<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['key' => 'app_name', 'value' => 'YurSayur'],
            ['key' => 'app_description', 'value' => ''],
            ['key' => 'app_logo', 'value' => 'public/yursayur-logo.png'],
        ];

        foreach ($data as $value) {
            Setting::updateOrCreate([
                'key' => $value['key'],
                'value' => $value['value'],
            ]);
        }
    }
}
