<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\ResponseOption;
use App\Models\Score;
use App\Models\Vocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vocations = Vocation::all()->keyBy('nome');

        $data = [
            [
                'conteudo' => 'Como você se sente em relação a explorar e testar novas ideias ou hipóteses?',
                'opcoes' => [
                    [
                        'conteudo' => 'Adoro experimentar e testar novas abordagens.',
                        'pontuacoes' => [
                            'Cientista de Dados / I.A' => 3,
                            'UX/UI' => 3,
                            'DevOps' => 2,
                        ],
                    ],
                    [
                        'conteudo' => 'Prefiro ter uma ideia clara antes de começar a agir.',
                        'pontuacoes' => [
                            'Programador Backend' => 3,
                            'QA' => 2,
                            'Gestão de Produtos' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Gosto de refinar ideias existentes em vez de criar novas.',
                        'pontuacoes' => [
                            'Programador Frontend' => 2,
                            'QA' => 3,
                            'Programador Backend' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'Em um projeto em equipe, qual papel você costuma assumir?',
                'opcoes' => [
                    [
                        'conteudo' => 'Coordenador, organizando o fluxo e tarefas.',
                        'pontuacoes' => [
                            'Gestão de Produtos' => 3,
                            'QA' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Analista, focando em detalhes e precisão.',
                        'pontuacoes' => [
                            'Cientista de Dados / I.A' => 3,
                            'QA' => 2,
                            'Programador Backend' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Criador, trazendo ideias visuais ou conceituais.',
                        'pontuacoes' => [
                            'UX/UI' => 3,
                            'Programador Frontend' => 2,
                            'Gestão de Produtos' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'O que mais te atrai em um novo desafio?',
                'opcoes' => [
                    [
                        'conteudo' => 'Entender o que o usuário precisa e como melhorar a experiência.',
                        'pontuacoes' => [
                            'UX/UI' => 3,
                            'Gestão de Produtos' => 2,
                            'Programador Frontend' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Resolver problemas complexos com uma abordagem prática.',
                        'pontuacoes' => [
                            'Programador Backend' => 3,
                            'Cientista de Dados / I.A' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Automatizar processos e aumentar a eficiência.',
                        'pontuacoes' => [
                            'DevOps' => 3,
                            'QA' => 2,
                            'Programador Backend' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'Como você reage a uma tarefa repetitiva?',
                'opcoes' => [
                    [
                        'conteudo' => 'Procuro um jeito de automatizar o processo.',
                        'pontuacoes' => [
                            'DevOps' => 3,
                            'QA' => 2,
                            'Cientista de Dados / I.A' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Tento aprimorar o método para otimizar o tempo.',
                        'pontuacoes' => [
                            'QA' => 3,
                            'Programador Backend' => 2,
                            'Gestão de Produtos' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Tento diversificar para manter o interesse.',
                        'pontuacoes' => [
                            'UX/UI' => 3,
                            'Programador Frontend' => 2,
                            'Cientista de Dados / I.A' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'Ao enfrentar uma dificuldade técnica, qual seria sua primeira reação?',
                'opcoes' => [
                    [
                        'conteudo' => 'Buscar tutoriais ou guias online para resolver o problema.',
                        'pontuacoes' => [
                            'Programador Backend' => 3,
                            'QA' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Analisar o problema para tentar resolver de forma estruturada.',
                        'pontuacoes' => [
                            'Cientista de Dados / I.A' => 3,
                            'QA' => 2,
                            'Gestão de Produtos' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Pedir ajuda ou discutir o problema com alguém.',
                        'pontuacoes' => [
                            'Gestão de Produtos' => 3,
                            'UX/UI' => 2,
                            'Programador Frontend' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'Você costuma revisar seu trabalho para buscar melhorias, mesmo que esteja pronto?',
                'opcoes' => [
                    [
                        'conteudo' => 'Sim, sempre vejo o que pode ser aprimorado.',
                        'pontuacoes' => [
                            'QA' => 3,
                            'Programador Backend' => 2,
                            'Cientista de Dados / I.A' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Apenas se eu tiver um motivo específico para revisar.',
                        'pontuacoes' => [
                            'Programador Frontend' => 3,
                            'UX/UI' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Prefiro seguir em frente para começar algo novo.',
                        'pontuacoes' => [
                            'Gestão de Produtos' => 3,
                            'UX/UI' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                ],
            ],
            [
                'conteudo' => 'Como você lida com a pressão de uma entrega importante?',
                'opcoes' => [
                    [
                        'conteudo' => 'Me organizo com antecedência para evitar estresse de última hora.',
                        'pontuacoes' => [
                            'Gestão de Produtos' => 3,
                            'QA' => 2,
                            'DevOps' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Trabalho bem sob pressão e gosto do desafio.',
                        'pontuacoes' => [
                            'Programador Backend' => 3,
                            'Cientista de Dados / I.A' => 2,
                            'UX/UI' => 1,
                        ],
                    ],
                    [
                        'conteudo' => 'Tento equilibrar prazos sem perder a qualidade do trabalho.',
                        'pontuacoes' => [
                            'UX/UI' => 3,
                            'Programador Frontend' => 2,
                            'Gestão de Produtos' => 1,
                        ],
                    ],
                ],
            ],
        ];

        foreach ($data as $perguntaData) {
            $pergunta = Question::create(['conteudo' => $perguntaData['conteudo']]);

            foreach ($perguntaData['opcoes'] as $opcaoData) {
                $opcao = ResponseOption::create([
                    'question_id' => $pergunta->id,
                    'conteudo' => $opcaoData['conteudo'],
                ]);

                foreach ($opcaoData['pontuacoes'] as $vocationNome => $pontos) {
                    Score::create([
                        'response_option_id' => $opcao->id,
                        'vocation_id' => $vocations[$vocationNome]->id,
                        'pontos' => $pontos,
                    ]);
                }
            }
        }
    }
}
