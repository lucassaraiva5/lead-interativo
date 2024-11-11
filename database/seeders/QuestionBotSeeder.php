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
            'Em um projeto em equipe, qual papel você costuma assumir?
1 - Coordenador, organizando o fluxo e tarefas.
2 - Analista, focando em detalhes e precisão.
3 - Criador, trazendo ideias visuais ou conceituais.

Digite apenas o numero da sua resposta',
            'O que mais te atrai em um novo desafio?
1 - Entender o que o usuário precisa e como melhorar a experiência.
2 - Resolver problemas complexos com uma abordagem prática.
3 - Automatizar processos e aumentar a eficiência.

Digite apenas o numero da sua resposta',
            'Como você reage a uma tarefa repetitiva?
1 - Procuro um jeito de automatizar o processo.
2 - Tento aprimorar o método para otimizar o tempo.
3 - Tento diversificar para manter o interesse.

Digite apenas o numero da sua resposta',
            'Ao enfrentar uma dificuldade técnica, qual seria sua primeira reação?
1 - Buscar tutoriais ou guias online para resolver o problema.
2 - Analisar o problema para tentar resolver de forma estruturada.
3 - Pedir ajuda ou discutir o problema com alguém.

Digite apenas o numero da sua resposta',
            ' Você costuma revisar seu trabalho para buscar melhorias, mesmo que esteja pronto?
1 - Sim, sempre vejo o que pode ser aprimorado.
2 - Apenas se eu tiver um motivo específico para revisar.
3 - Prefiro seguir em frente para começar algo novo.

Digite apenas o numero da sua resposta',
            'Como você lida com a pressão de uma entrega importante?
1 - Me organizo com antecedência para evitar estresse de última hora.
2 - Trabalho bem sob pressão e gosto do desafio.
3 - Tento equilibrar prazos sem perder a qualidade do trabalho.

Digite apenas o numero da sua resposta',
            'Me envie por favor uma selfie onde apareça o seu rosto para que eu possa lhe enviar uma surpresa!',
        ];

        foreach ($perguntas as $key => $texto) {
            $pergunta = QuestionBot::create(['question' => $texto, 'order' => $key]);
        }
    }
}
