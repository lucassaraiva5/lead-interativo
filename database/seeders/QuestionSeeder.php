<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perguntas = [
            'Como você se sente em relação a explorar e testar novas ideias ou hipóteses?',
            'Em um projeto em equipe, qual papel você costuma assumir?',
            'O que mais te atrai em um novo desafio?',
            'Como você reage a uma tarefa repetitiva?',
            'Ao enfrentar uma dificuldade técnica, qual seria sua primeira reação?',
            ' Você costuma revisar seu trabalho para buscar melhorias, mesmo que esteja pronto?',
            'Como você lida com a pressão de uma entrega importante?'
        ];

        foreach ($perguntas as $texto) {
            $pergunta = Question::create(['conteudo' => $texto]);

            // Associe a pergunta a uma ou mais vocações relevantes
            switch ($texto) {
                case 'Eu gosto de analisar dados e encontrar padrões escondidos.':
                    $pergunta->vocations()->attach(Vocation::where('nome', 'Cientista de Dados')->first());
                    break;
                case 'Tenho interesse em criar interfaces intuitivas para usuários.':
                    $pergunta->vocations()->attach(Vocation::where('nome', 'UX/UI')->first());
                    break;
                // Continue associando as perguntas às vocações apropriadas
                // ...
            }
        }
    }
}
