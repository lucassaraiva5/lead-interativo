<?php

namespace Database\Seeders;

use App\Models\Vocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vocations = [
            ['nome' => 'Cientista de Dados / I.A'],
            ['nome' => 'UX/UI'],
            ['nome' => 'Programador Backend'],
            ['nome' => 'Programador Frontend'],
            ['nome' => 'QA'],
            ['nome' => 'Gestão de Produtos'],
            ['nome' => 'DevOps'],
        ];

        foreach ($vocations as $vocation) {
            Vocation::create($vocation);
        }
    }
}
