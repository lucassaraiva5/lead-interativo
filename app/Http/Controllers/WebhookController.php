<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Questionary;
use App\Models\QuestionBot;
use App\Models\UserQuestionStatus;
use App\Models\UserResponse;
use App\Models\Vocation;
use App\Services\AIServiceIntegration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info("Mandou mensagem", [$request->all()]);

        $event = $request->all();
        $from = $event["data"]["from"];
        $to = $event["data"]["from"];
        $body = $event["data"]["body"];
        $media = $event["data"]["media"];
        $receivedAt = now();

        // Salva a mensagem no banco de dados
        $message = Message::create([
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'received_at' => $receivedAt,
            'media' => $media
        ]);

        $this->process_message($message);

        // Retorna uma resposta para o UltraMsg
        return response("", 200);
    }

    public function sendMessage(Message $message)
    {
      if($message->from === "555182688209@c.us") {
        $token = config('services.whatsapp.token');
        $instance = config('services.whatsapp.instance');

        $to = str_replace("@c.us", "", $message->from);
        $params=array(
            'token' => $token,
            'to' => '+' . $to,
            'body' => $message->body,
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://api.ultramsg.com/".$instance."/messages/chat",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => http_build_query($params),
          CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
          ),
        ));
            
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
          echo "cURL Error #:" . $err;
        } else {
          echo $response;
        }
      }
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
        $vocation =  Vocation::find($vocationId);

        return $vocation;
    }

    public function process_message(Message $message)
    {
      $userQuestionStatus = UserQuestionStatus::where('number', $message->from)->first();

      if ($userQuestionStatus == null) {
          $questionario = Questionary::create();
          $userQuestionStatus = UserQuestionStatus::create([
            'number'=> $message->from,
            'current_question'=> -1,
            'questionary_id' => $questionario->id
          ]);
      }

      $questionario = Questionary::find($userQuestionStatus->questionary_id)->first();
      $questaoAtual = $userQuestionStatus->current_question;

      Log::info('Questao atual: '. $questaoAtual);
      Log::info('Mensagem: '. $message);

      if($questaoAtual === 0) {
         $userQuestionStatus->name = $message->body;
         $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
         $userQuestionStatus->save();
      } else if ($questaoAtual > 0 && $questaoAtual <= 7) {
          if($message->body !== "1" && $message->body !== "2" && $message->body !== "3") {
              $message->body = "Opção inválida.";
              $this->sendMessage($message);
          }else{
              $responseOption = intval($message->body) + 1;
              UserResponse::create([
                'questionary_id' => $questionario->id,
                'question_id' => $questaoAtual,
                'response_option_id' => $responseOption,
              ]);
              $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
              $userQuestionStatus->save();
         }
      } else if($questaoAtual == 8) {
        if($message->media == null) {
          $message->body = "Desculpe essa nao é uma imagem valida";
          $this->sendMessage($message);
        } else {
          $userQuestionStatus->image_sent = $message->media;
          $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
          $userQuestionStatus->save();
          $message->body = "Estou trabalhando no resultado, por favor aguarde. Irei lhe enviar uma mensagem assim que finalizar";
          $this->sendMessage($message);
          $imageLink = AIServiceIntegration::generateImage($message->media);
          $vocacao = $this->calcularResultado($questionario);
          $userQuestionStatus->image_generated = $imageLink;
          $userQuestionStatus->vocation = $vocacao->nome;
          $userQuestionStatus->save();
          $message->body = $userQuestionStatus->image_generated;
          $this->sendMessage($message);
          $message->body = "O resultado do seu testr foi: " .$userQuestionStatus->vocation;
          $this->sendMessage($message);
          return;
        }
        
      } else if($questaoAtual > 8) {
        if($userQuestionStatus->image_generated == null) {
          $message->body = "Estou trabalhando no resultado, por favor aguarde. Irei lhe enviar uma mensagem assim que finalizar";
          $this->sendMessage($message);
        }else {
          $message->body = $userQuestionStatus->image_generated;
          $this->sendMessage($message);

          $message->body = $userQuestionStatus->vocation;
          $this->sendMessage($message);
        }
        
        return;
      }

      if($userQuestionStatus->current_question === -1) {
        $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
        $userQuestionStatus->save();
      }

      $question = QuestionBot::where(column: 'order', operator: "=", value: $userQuestionStatus->current_question)->first();
      $message->body = $question->question;
      $this->sendMessage($message);
    }
}
