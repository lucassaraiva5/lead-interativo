<?php

namespace App\Http\Controllers;

use App\Models\Message;
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
      
      if($message->from === "555182688209@c.us") {
          $this->sendMessage($message);
      }
    }
}
