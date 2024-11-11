<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\QuestionBot;
use App\Models\UserQuestionStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info("Mandou mensagem", [$request->all()]);

        $event = $request->all();
        $from = $event["data"]["from"];
        $to = $event["data"]["from"];
        $body = $event["data"]["body"];
        $receivedAt = now();

        // Salva a mensagem no banco de dados
        $message = Message::create([
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'received_at' => $receivedAt,
        ]);

        $this->process_message($message);

        // Retorna uma resposta para o UltraMsg
        return response("", 200);
    }

    public function sendMessage(Message $message)
    {
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

    public function process_message(Message $message)
    {
      $userQuestionStatus = UserQuestionStatus::where('number', $message->from)->first();

      if ($userQuestionStatus == null) {
          $userQuestionStatus = UserQuestionStatus::create([
            'number'=> $message->from,
            'current_question'=> -1,
          ]);
      }

      $questaoAtual = $userQuestionStatus->current_question;

      Log::info('Questao atual: '. $questaoAtual);

      if($questaoAtual === 0) {
         //salva o nome
         $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
         $userQuestionStatus->save();
      } else if ($questaoAtual > 0 && $questaoAtual <= 7) {
          if($message->body !== "1" && $message->body !== "2" && $message->body !== "3") {
              $message->body = "Opção inválida.";
              $this->sendMessage($message);
          }else{
              //salva a resposta
              $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
              $userQuestionStatus->save();
         }
      } else if($questaoAtual == 8) {
        $message->body = "Desculpe essa nao é uma imagem valida";
        $this->sendMessage($message);
      } else if($questaoAtual > 8) {
        $message->body = "Estou trabalhando no resultado, por favor aguarde. Irei lhe enviar uma mensagem assim que finalizar";
        $this->sendMessage($message);
      }

      if($userQuestionStatus->current_question === -1) {
        $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
        $userQuestionStatus->save();
      }

      $question = QuestionBot::where(column: 'order', operator: "=", value: $userQuestionStatus->current_question)->first();
      $message->body = $question->question;
      
      if($message->from === "555182688209@c.us") {
          $this->sendMessage($message);
      }
    }
}
