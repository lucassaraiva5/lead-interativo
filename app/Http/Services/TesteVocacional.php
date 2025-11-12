<?php

namespace App\Http\Services;

class TesteVocacional
{
    private $perguntas = [
        1 => ["pergunta"=> "O que você prefere:", "opcoes"=> [
            [
                "texto" => "Lidar com maquinas",
                "tipo" => "redireciona",
                "pergunta_id" => 2
            ],
            [
                "texto" => "Lidar com pessoas",
                "tipo" => "redireciona",
                "pergunta_id" => 5
            ]
        ]],
        2 => ["pergunta"=> "O que você prefere:", "opcoes"=> [
            [
                "texto" => "Criar sistemas",
                "tipo" => "redireciona",
                "pergunta_id" => 3
            ],
            [
                "texto" => "Analisar dados",
                "tipo" => "resposta",
                "valor" => "Cientista de Dados / I.A"
            ],
        ]],
        3 => ["pergunta"=> "O que você prefere:", "opcoes"=> [
            [
                "texto" => "O que é visivel para o Usuário",
                "tipo" => "resposta",
                "valor" => "Programador Frontend"
            ],
            [
                "texto" => "Logica interna do sistema",
                "tipo" => "redireciona",
                "pergunta_id" => 4
            ],
        ]],
        4 => ["pergunta"=> "O que você prefere:", "opcoes"=> [
            [
                "texto" => "Infraestrutura, automação e servidores",
                "tipo" => "resposta",
                "valor" => "Devops"
            ],
            [
                "texto" => "Regras, banco de dados e integrações",
                "tipo" => "resposta",
                "valor" => "Programador Backend"
            ],
        ]],
        5 => ["pergunta"=> "Você gostaria de testar sistemas e encontrar falhas?", "opcoes"=> [
            [
                "texto" => "Sim",
                "tipo" => "resposta",
                "valor" => "QA (Quality Assurance)"
            ],
            [
                "texto" => "Regras, banco de dados e integrações",
                "tipo" => "redireciona",
                "pergunta_id" => 6
            ],
        ]],
        6 => ["pergunta"=> "O que você prefere:", "opcoes"=> [
            [
                "texto" => "Criação de interfaces e usabilidade",
                "tipo" => "resposta",
                "valor" => "UX/UI"
            ],
            [
                "texto" => "Planejamento, priorização e liderança",
                "tipo" => "resposta",
                "valor" => "Gestão de Produtos"
            ],
        ]],
    ];

    
}
