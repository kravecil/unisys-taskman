<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\UserType;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [ 'creator',    'создатель'     ],
            [ 'executor',   'исполнитель'   ],
            [ 'coexecutor', 'соисполнитель' ],
            [ 'controller', 'наблдатель'    ],
        ];
        foreach($types as $type) {
            UserType::create([
                'name' => $type[0],
                'title' => $type[1],
            ]);
        };
    }
}
