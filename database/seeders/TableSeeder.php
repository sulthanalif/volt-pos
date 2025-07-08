<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            [
                'number' => 1,
                'location' => 'Indoor',
                'capacity' => 2,
            ],
            [
                'number' => 2,
                'location' => 'Indoor',
                'capacity' => 4,
            ],
            [
                'number' => 3,
                'location' => 'Indoor',
                'capacity' => 6,
            ],
            [
                'number' => 4,
                'location' => 'Indoor',
                'capacity' => 2,
            ],
            [
                'number' => 5,
                'location' => 'Outdoor',
                'capacity' => 4,
            ],
            [
                'number' => 6,
                'location' => 'Outdoor',
                'capacity' => 4,
            ],
            [
                'number' => 7,
                'location' => 'Outdoor',
                'capacity' => 6,
            ],
            [
                'number' => 8,
                'location' => 'VIP Room',
                'capacity' => 8,
            ],
            [
                'number' => 9,
                'location' => 'VIP Room',
                'capacity' => 8,
            ],
            [
                'number' => 10,
                'location' => 'VIP Room',
                'capacity' => 10,
            ],
        ];

        foreach ($tables as $table) {
            Table::create($table);
        }
    }
}
