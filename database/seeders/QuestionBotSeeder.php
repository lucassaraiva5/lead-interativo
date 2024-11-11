<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionBot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionBotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perguntas = [
            'Qual seu nome?',
            'Como você se sente em relação a explorar e testar novas ideias ou hipóteses?
1 - Adoro experimentar e testar novas abordagens.
2 - Prefiro ter uma ideia clara antes de começar a agir.
3 - Gosto de refinar ideias existentes em vez de criar novas.

Digite apenas o numero da sua resposta',
            'Em um projeto em equipe, qual papel você costuma assumir?',
            'O que mais te atrai em um novo desafio?',
            'Como você reage a uma tarefa repetitiva?',
            'Ao enfrentar uma dificuldade técnica, qual seria sua primeira reação?',
            ' Você costuma revisar seu trabalho para buscar melhorias, mesmo que esteja pronto?',
            'Como você lida com a pressão de uma entrega importante?'
        ];

        foreach ($perguntas as $key => $texto) {
            $pergunta = QuestionBot::create(['question' => $texto, 'order' => $key]);
        }
    }
}
