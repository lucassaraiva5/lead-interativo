<?php

namespace App\Services;

use GuzzleHttp\Client;

class AIServiceIntegration {
    public static function generateImage()
    {
        $client = new Client();
        $apiKey = env('OPENAI_API_KEY'); 

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
                                'text' => "Don't need to tell who is the person on the image, just all appearance details and age to I can create a character from a book and ignoring the background and just respond with those details",
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'https://assets2.cbsnewsstatic.com/hub/i/r/2019/01/26/1c2d45b1-af86-4091-bef3-a19e86155131/thumbnail/1280x720/56ca4f21d138749a63a89fd5f1ca09a5/0126-satmo-wikipediaeditor-barnett-1767717-640x360.jpg?v=379420b9063a2aadbcd559df18e2d1ae'
                                ]
                            ]
                        ]
                    ],
                    
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        $imageDescription = $data['choices'][0]['message']['content'];

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