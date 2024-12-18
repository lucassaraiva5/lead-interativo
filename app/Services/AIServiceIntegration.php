<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AIServiceIntegration {


    public static function getImageDescription(string $fileName)
    {
        $client = new Client();
        $apiKey = config('services.api.key');
        
        //$caminhoImagem = base_path('public/storage/' . $fileName);
        //$imagemBase64 = base64_encode(file_get_contents($caminhoImagem));

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
                                    'url' => $fileName
                                ]
                                // 'image_url' => [
                                //     'url' => "data:image/jpeg;base64,". $imagemBase64
                                // ]
                            ]
                        ]
                    ],
                    
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['choices'][0]['message']['content'];
    }

    public static function generateImage(string $fileName, int $id)
    {
        set_time_limit(-1);
        $client = new Client();
        $apiKey = config('services.api.key');
        //$apiKey = env('OPENAI_API_KEY'); 
        
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
        $image = file_get_contents($imageData["data"][0]["url"]);

        $imagemRedimensionada = Image::read($image)->scale(616,616);;
        $imageName = "image_" . uniqid() . '.jpg';
        Storage::disk('public')->put($imageName, (string) $imagemRedimensionada->encode());

        $backgroundPath = resource_path("image/{$id}.png");
        $overlayPath = storage_path('app/public/' . $imageName);

        $overlayInfo = getimagesize($overlayPath);
        $overlayMime = $overlayInfo['mime'];

        $background = imagecreatefrompng($backgroundPath);

        if ($overlayMime === 'image/jpeg') {
            $overlay = imagecreatefromjpeg($overlayPath);
        } elseif ($overlayMime === 'image/png') {
            $overlay = imagecreatefrompng($overlayPath);
        } else {
            throw new \Exception('Formato de imagem não suportado. Apenas JPEG e PNG são permitidos.');
        }

        $overlayWidth = imagesx($overlay);
        $overlayHeight = imagesy($overlay);

        imagealphablending($background, true);
        imagesavealpha($background, true);
        $xPosition = 232; // posição X no background
        $yPosition = 270; // posição Y no background

        imagecopy($background, $overlay, $xPosition, $yPosition, 0, 0, $overlayWidth, $overlayHeight);
        $uniqueId = uniqid();
        $outputFilename = "output_{$uniqueId}.png";
        $outputPath = storage_path("app/public/{$outputFilename}");
        imagepng($background, $outputPath);

        imagedestroy($background);
        imagedestroy($overlay);

        return $outputFilename;
    }
}