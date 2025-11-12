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

        $from = $event["data"]["from"] ?? null;
        $to = $event["data"]["to"] ?? $from;
        $body = $event["data"]["body"] ?? '';
        $media = $event["data"]["media"] ?? null;
        $receivedAt = now();

        if (!$from) {
            Log::error('Webhook recebido sem número de origem', ['event' => $event]);
            return response("Missing sender", 400);
        }

        // Salva a mensagem
        $message = Message::create([
            'from' => $from,
            'to' => $to,
            'body' => $body,
            'received_at' => $receivedAt,
            'media' => $media
        ]);

        $this->process_message($message);
        return response("", 200);
    }

    public function sendMessage(Message $message)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('http://localhost:3000/send-message', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'number' => str_replace('@c.us', '', $message->from),
                    'message' => $message->body
                ],
                'http_errors' => false
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erro ao enviar mensagem WhatsApp: ' . $response->getBody());
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem WhatsApp: ' . $e->getMessage());
        }
    }

    public function sendImageMessage(Message $message, $image, $caption = "", $priority = 10, $referenceId = "", $nocache = false)
    {
        $to = str_replace("@c.us", "", $message->from);
        $params = [
            "to" => $to,
            "caption" => $caption,
            "image" => $image,
            "priority" => $priority,
            "referenceId" => $referenceId,
            "nocache" => $nocache
        ];

        return $this->sendRequest("POST", "messages/image", $params);
    }

    public function sendRequest($method, $path, $params = [])
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post('http://localhost:3000/send-message', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => [
                    'number' => $params['to'],
                    'message' => $params['caption'] ?? '',
                    'image' => $params['image'] ?? null
                ],
                'http_errors' => false
            ]);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erro ao enviar mensagem WhatsApp: ' . $response->getBody());
                return ["Error" => $response->getBody()];
            }

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Erro ao enviar mensagem WhatsApp: ' . $e->getMessage());
            return ["Error" => $e->getMessage()];
        }
    }

    public function process_message(Message $message)
    {
        $userQuestionStatus = UserQuestionStatus::firstOrCreate(
            ['number' => $message->from],
            ['current_question' => -1, 'current_random_question' => rand(1, 7)]
        );

        $questaoAtual = $userQuestionStatus->current_question;

        // 🔹 Se já terminou o fluxo, apaga o registro e encerra
        if ($questaoAtual > 9) {
            $userQuestionStatus->delete();

            $message->body = "✅ Obrigado por participar! Envie qualquer mensagem para começar novamente.";
            $this->sendMessage($message);
            return;
        }

        // 🔹 Se é o primeiro contato, envia a primeira pergunta
        if ($questaoAtual === -1) {
            $userQuestionStatus->update(['current_question' => 0]);
            $question = QuestionBot::where('order', 0)->first();

            $message->body = $question ? $question->question : "Olá! Qual é o seu nome?";
            $this->sendMessage($message);
            return;
        }

        // 🔹 Etapa 0: recebe o nome
        if ($questaoAtual === 0) {
            $userQuestionStatus->name = $message->body;
            $userQuestionStatus->increment('current_question');
            $userQuestionStatus->save();
        }

        // 🔹 Etapas 1 a 5: perguntas de múltipla escolha
        elseif ($questaoAtual > 0 && $questaoAtual <= 5) {
            if (!in_array($message->body, ['1', '2', '3'])) {
                $message->body = "⚠️ Opção inválida. Responda com 1, 2 ou 3.";
                $this->sendMessage($message);
                return;
            }

            $userQuestionStatus->increment('current_question');

            $next = $userQuestionStatus->current_random_question + 1;
            if ($next > 7) $next = 1;
            $userQuestionStatus->current_random_question = $next;
            $userQuestionStatus->save();
        }

        // 🔹 Etapa 6: Instagram
        elseif ($questaoAtual === 6) {
            $userQuestionStatus->instagram = $message->body;
            $userQuestionStatus->increment('current_question');
            $userQuestionStatus->save();
        }

        // 🔹 Etapa 7: Escola
        elseif ($questaoAtual === 7) {
            $userQuestionStatus->school = $message->body;
            $userQuestionStatus->increment('current_question');
            $userQuestionStatus->save();
        }

        elseif ($questaoAtual === 8) {
            if (!in_array($message->body, ['1', '2'])) {
                $message->body = "⚠️ Opção inválida. Responda com 1 ou 2.";
                $this->sendMessage($message);
                return;
            }

            $userQuestionStatus->preferred_style = $message->body;
            $userQuestionStatus->increment('current_question');
            $userQuestionStatus->save();
        }

        // 🔹 Etapa 9: Imagem / Resultado
        elseif ($questaoAtual === 9) {
            if (!$message->media) {
                $message->body = "❌ Por favor, envie uma imagem válida (selfie).";
                $this->sendMessage($message);
                return;
            }

            $userQuestionStatus->image_sent = $message->media;
            $userQuestionStatus->increment('current_question');
            $userQuestionStatus->save();

            $this->sendMessage($message->fill(['body' => "⏳ Gerando seu resultado, aguarde um momento..."]));

            // gera imagem e resultado aleatório
            $vocacao = Vocation::find(rand(1, 7));
            Log::info('Gerando imagem para vocação', ['vocacao_id' => $vocacao->id, 'media' => $message->media]);
            $imagePath = AIServiceIntegration::generateImage($message->media, $vocacao->id, $userQuestionStatus->preferred_style);
            Log::info('Imagem gerada com sucesso', ['image_path' => $imagePath]);

            $userQuestionStatus->update([
                'image_generated' => $imagePath,
                'vocation' => $vocacao->nome,
            ]);

            $path = Storage::disk('public')->path($imagePath);
            Log::info('Preparando para enviar imagem', ['full_path' => $path]);
            
            if (!file_exists($path)) {
                Log::error('Arquivo de imagem não encontrado', ['path' => $path]);
                $message->body = "❌ Desculpe, ocorreu um erro ao gerar sua imagem. Por favor, tente novamente.";
                $this->sendMessage($message);
                return;
            }

            $imageContent = file_get_contents($path);
            if ($imageContent === false) {
                Log::error('Não foi possível ler o arquivo de imagem', ['path' => $path]);
                $message->body = "❌ Desculpe, ocorreu um erro ao processar sua imagem. Por favor, tente novamente.";
                $this->sendMessage($message);
                return;
            }

            $mime = mime_content_type($path); // detecta automaticamente image/jpeg, image/png etc.
            $base64Image = 'data:' . $mime . ';base64,' . base64_encode($imageContent);
            Log::info('Enviando imagem', ['size' => strlen($base64Image)]);
            
            $result = $this->sendImageMessage($message, $base64Image);
            Log::info('Resultado do envio da imagem', ['result' => $result]);

            $message->body = "🎯 O resultado do seu teste foi: *{$vocacao->nome}*.
    Compartilhe nos stories e marque @computacaotorres para concorrer a uma Alexa!";
            $this->sendMessage($message);

            // 🔹 Apaga o usuário no fim (permite refazer)
            $userQuestionStatus->delete();
            return;
        }

        // 🔹 Próxima pergunta (baseado na etapa atual)
        $q = $userQuestionStatus->current_question;
        $question = null;

        if ($q === 0) {
            $question = QuestionBot::where('order', 0)->first();
        } elseif ($q >= 1 && $q <= 5) {
            $question = QuestionBot::where('order', $userQuestionStatus->current_random_question)->first();
        }

        if ($question) {
            $message->body = $question->question;
        } elseif ($q === 6) {
            $message->body = "📸 Qual o seu Instagram? (para validação do sorteio da Alexa)";
        } elseif ($q === 7) {
            $message->body = "🏫 Qual sua escola?";
        } elseif ($q === 8) {
            $message->body = "Vamos criar uma foto incrivel de você, mas antes, precisamos saber qual estilo você prefere?\n1. Foto no estilo PIXAR\n2. Você na carreira daqui a 10 anos\nDigite apenas o numero da opção.";
        } elseif ($q === 9) {
            $message->body = "🤳 Envie uma selfie para gerar sua imagem estilo escolhido!";
        } else {
            $message->body = "✅ Obrigado por participar!";
        }

        $this->sendMessage($message);
    }

}
