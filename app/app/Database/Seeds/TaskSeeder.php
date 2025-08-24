<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run()
    {
        $data = [
            ['title' => 'Comprar leche', 'completed' => 0],
            ['title' => 'Escribir README', 'completed' => 1],
            ['title' => 'Terminar CRUD', 'completed' => 0],
        ];

        $this->db->table('tasks')->insertBatch($data);
    }
}
