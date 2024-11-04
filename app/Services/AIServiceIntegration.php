<?php

namespace App\Services;

use GuzzleHttp\Client;

class AIServiceIntegration {


    public static function getImageDescription(string $fileName)
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY'); 
        
        $caminhoImagem = base_path('public/storage/' . $fileName);
        $imagemBase64 = base64_encode(file_get_contents($caminhoImagem));

        $response = $client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Don't need to tell who is the person on the image, just all appearence details and ficticious age and gender to I can create a character to my book and ignoring the background and just respond with those details",
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,". $imagemBase64
                                ]
                            ]
                        ]
                    ],
                    
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['choices'][0]['message']['content'];
    }

    public static function generateImage(string $fileName)
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY'); 
        
        for ($i=0; $i < 5; $i++) { 
            $imageDescription = AIServiceIntegration::getImageDescription($fileName);
            if(!str_contains($imageDescription, "I'm")) {
                break;
            }
        }

        // Faça outra chamada para gerar a imagem usando a descrição
        $responseImage = $client->post('https://api.openai.com/v1/images/generations', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'dall-e-3',
                'prompt' => "Crie um personagem pixar 3d inspirado na seguinte descrição: " . $imageDescription . ". O fundo da imagem deve representar que ele é um programador backend.",
            ],
        ]);

        $imageData = json_decode($responseImage->getBody(), true);

        return $imageData["data"][0]["url"];
    }
}