<?php

namespace App\Livewire;

use App\Models\Question;
use App\Models\Questionary;
use App\Models\UserResponse;
use App\Models\Vocation;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TesteVocacional extends Component
{
    public $perguntas;
    public $respostas = [];
    public $indiceAtual = 0; // Controla qual pergunta está sendo exibida
    public $resultado;

    public $avatar;

    public function mount()
    {
        $this->perguntas = Question::with('responseOptions')->get();
    }

    public function proximaPergunta()
    {
        // Validação para a pergunta atual
        $perguntaAtual = $this->perguntas[$this->indiceAtual];
        if (!isset($this->respostas[$perguntaAtual->id])) {
            $this->addError('respostas.' . $perguntaAtual->id, 'Esta pergunta é obrigatória.');
            return;
        }

        // Avança para a próxima pergunta
        if ($this->indiceAtual < count($this->perguntas) - 1) {
            $this->indiceAtual++;
        } else {
            $this->enviarRespostas(); // Chama o método de envio quando a última pergunta é respondida
        }
    }

    public function enviarRespostas()
    {
        // Criar um novo questionário
        $questionario = Questionary::create();

        // Salvar as respostas
        foreach ($this->respostas as $perguntaId => $opcaoRespostaId) {
            UserResponse::create([
                'questionary_id' => $questionario->id,
                'question_id' => $perguntaId,
                'response_option_id' => $opcaoRespostaId,
            ]);
        }

        // Calcular o resultado
        $this->calcularResultado($questionario);
    }

    public function calcularResultado($questionario)
    {
        $pontuacoes = [];

        $respostasUsuario = $questionario->userResponses()->with('responseOption.pontuacoes.vocation')->get();

        foreach ($respostasUsuario as $respostaUsuario) {
            $pontuacoesOpcao = $respostaUsuario->responseOption->pontuacoes;

            foreach ($pontuacoesOpcao as $pontuacao) {
                $vocationId = $pontuacao->vocation->id;
                if (!isset($pontuacoes[$vocationId])) {
                    $pontuacoes[$vocationId] = 0;
                }
                $pontuacoes[$vocationId] += $pontuacao->pontos;
            }
        }

        // Encontrar a vocação com a maior pontuação
        $vocationId = array_keys($pontuacoes, max($pontuacoes))[0];
        $this->resultado = Vocation::find($vocationId);

        $user = Auth::user();
        $this->avatar = $user->avatar;
    }

    public function reiniciarTeste()
    {
        $this->respostas = [];
        $this->indiceAtual = 0;
        $this->resultado = null;
        $this->avatar = null;
    }

    public function render()
    {
        return view('livewire.teste-vocacional');
    }
}
