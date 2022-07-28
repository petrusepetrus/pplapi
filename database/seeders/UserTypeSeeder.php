<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types=[
            ['user_type'=>'Pilates Client'],
            ['user_type'=>'Physio Client'],
            ['user_type'=>'Pilates Instructor'],
            ['user_type'=>'Studio Pilates Teacher'],
            ['user_type'=>'Studio Physio'],
            ['user_type'=>'Studio Instructor Trainer']
        ];
        DB::table('user_types')->insert($types);
    }
}
