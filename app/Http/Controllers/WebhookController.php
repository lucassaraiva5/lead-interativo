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
      //if($message->from === "555182688209@c.us") {
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
      //}
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

    public function sendImageMessage(Message $message,$image,$caption="",$priority=10,$referenceId="",$nocache=false){
        $to = str_replace("@c.us", "", $message->from);
	    $params =array("to"=>$to,"caption"=>$caption,"image"=>$image,"priority"=>$priority,"referenceId"=>$referenceId,"nocache"=>$nocache);
		return $this->sendRequest("POST","messages/image",$params );
	}

    public function sendRequest($method,$path,$params=array()){

        $token = config('services.whatsapp.token');
        $instance = config('services.whatsapp.instance');

        if(!is_callable('curl_init')){
            return array("Error"=>"cURL extension is disabled on your server");
        }
        $url="https://api.ultramsg.com/".$instance."/".$path;
        $params['token'] = $token;
        $data=http_build_query($params);
        if(strtolower($method)=="get")$url = $url . '?' . $data;
        $curl = curl_init($url);
        if(strtolower($method)=="post"){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        }	 
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if($httpCode == 404) {
            return array("Error"=>"instance not found or pending please check you instance id");
        }
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        curl_close($curl);
        
        if (strpos($contentType,'application/json') !== false) {
            return json_decode($body,true);
        }
        return $body;
    }

    public function process_message(Message $message)
    {
        $userQuestionStatus = UserQuestionStatus::where('number', "=",$message->from)->first();

        if ($userQuestionStatus == null) {
            $userQuestionStatus = UserQuestionStatus::create([
                'number'=> $message->from,
                'current_question'=> -1,
                'current_random_question' => rand(1,7),
            ]);
        }

        $questaoAtual = $userQuestionStatus->current_question;

        if($questaoAtual === 0) {
            $userQuestionStatus->name = $message->body;
            $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
            $userQuestionStatus->save();
        } else if ($questaoAtual > 0 && $questaoAtual <= 5) {
            if($message->body !== "1" && $message->body !== "2" && $message->body !== "3") {
                $message->body = "Opção inválida.";
                $this->sendMessage($message);
            }else{
                $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
                $nextRandomQuestion = $userQuestionStatus->current_random_question + 1;
                if($nextRandomQuestion > 7) {
                    $nextRandomQuestion = 1;
                }
                $userQuestionStatus->current_random_question = $nextRandomQuestion;
                $userQuestionStatus->save();
            }
        }else if($questaoAtual == 6) {
            $userQuestionStatus->instagram = $message->body;
            $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
            $userQuestionStatus->save();
        }else if($questaoAtual == 7) {
            $userQuestionStatus->school = $message->body;
            $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
            $userQuestionStatus->save();
        }else if($questaoAtual == 8) {
            if($message->media == null) {
                $message->body = "Desculpe essa nao é uma imagem valida";
                $this->sendMessage($message);
            } else {
                $userQuestionStatus->image_sent = $message->media;
                $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
                $userQuestionStatus->save();
                $message->body = "Estou trabalhando no resultado, por favor aguarde. Irei lhe enviar uma mensagem assim que finalizar";
                $this->sendMessage($message);
                $vocacaoId = rand(1, 7);
                $vocacao = Vocation::find($vocacaoId);
                $imagePath = AIServiceIntegration::generateImage($message->media, $vocacao->id);
                $userQuestionStatus->image_generated = $imagePath;
                $userQuestionStatus->vocation = $vocacao->nome;
                $userQuestionStatus->save();
                $path = Storage::disk('public')->path($userQuestionStatus->image_generated);
                $this->sendImageMessage($message, base64_encode(file_get_contents($path)));
                $message->body = "O resultado do seu teste foi: " .$userQuestionStatus->vocation. ".
        Compartilhe seu resultado nos stories do Instagram, marcando @computacaotorres, e siga o perfil @computacaotorres.
        Cumprindo esses passos, você concorre a uma Alexa!";
                $this->sendMessage($message);
                return;
            }
        } else if($questaoAtual > 8) {
            if($userQuestionStatus->image_generated == null) {
                $message->body = "Estou trabalhando no resultado, por favor aguarde. Irei lhe enviar uma mensagem assim que finalizar";
                $this->sendMessage($message);
            }else {
                $path = Storage::disk('public')->path($userQuestionStatus->image_generated);

                $message->body = $userQuestionStatus->image_generated;
                $this->sendImageMessage($message, base64_encode(file_get_contents($path)));

                $message->body = "O resultado do seu teste foi: " .$userQuestionStatus->vocation. ".
        Compartilhe seu resultado nos stories do Instagram, marcando @computacaotorres, e siga o perfil @computacaotorres.
        Cumprindo esses passos, você concorre a uma Alexa!";
                $this->sendMessage($message);
            }
            
            return;
        }

        if($userQuestionStatus->current_question === -1) {
            $userQuestionStatus->current_question = $userQuestionStatus->current_question + 1;
            $userQuestionStatus->save();
        }

        if($userQuestionStatus->current_question === 0) {
            $question = QuestionBot::where(column: 'order', operator: "=", value: 0)->first();
            $message->body = $question->question;
            $this->sendMessage($message);
        }else if($userQuestionStatus->current_question >= 1 && $userQuestionStatus->current_question <= 5) {
            $question = QuestionBot::where(column: 'order', operator: "=", value: $userQuestionStatus->current_random_question)->first();
            $message->body = $question->question;
            $this->sendMessage($message);
        }else if($userQuestionStatus->current_question === 6) {
            $message->body = "Qual seu instagram? (Para validação do sorteio da ALEXA)";
            $this->sendMessage($message);
        }else if ($userQuestionStatus->current_question === 7) {
            $message->body = "Qual sua escola?";
            $this->sendMessage($message);
        }else if ($userQuestionStatus->current_question === 8) {
            $message->body = "Me envie por favor uma selfie onde apareça o seu rosto para que eu possa gerar uma imagem sua estilo PIXAR com inteligencia artificial";
            $this->sendMessage($message);
        }
        
    }
}
