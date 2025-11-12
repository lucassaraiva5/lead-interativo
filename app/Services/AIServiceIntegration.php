<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

class AIServiceIntegration {

    private const MAX_IMAGE_SIZE = 1024;
    private const MAX_FILE_SIZE = 4 * 1024 * 1024; // 4MB

    public static function generateImage(string $fileName, int $id, int $idPrompt)
    {
        set_time_limit(-1);
        $client = new Client();
        $apiKey = config('services.api.key');

        switch ($idPrompt) {
            case 1:
                $promptContent = 'Transform this person into a Pixar 3D style programmer character preserving the maximum of the image traits with a programming background containing code snippets on screens, computer monitors, tech gadgets.';
                break;
            case 2:
                $promptContent = 'Imagine this person 10 years in the future, highly successful in the technology field. Depict them as a visionary tech leader, surrounded by advanced technology, futuristic devices, and digital innovation. The background should feature an iconic cityscape from either Dubai, the United States, London, or Singapore, symbolizing global success and modern achievement. Maintain the person\'s key facial traits and professional appearance, with a vibrant, inspiring atmosphere.';
                break;
            
            default:
                $promptContent = 'Imagine this person 10 years in the future, highly successful in the technology field. Depict them as a visionary tech leader, surrounded by advanced technology, futuristic devices, and digital innovation. The background should feature an iconic cityscape from either Dubai, the United States, London, or Singapore, symbolizing global success and modern achievement. Maintain the person\'s key facial traits and professional appearance, with a vibrant, inspiring atmosphere.';
                break;
        }

        try {
            $tempImagePng = self::prepareImage($fileName);
            $outputImage = self::callOpenAIAPI($client, $apiKey, $tempImagePng, $promptContent);
            $processedImage = self::processAndSaveOutput($outputImage);
            $finalImage = self::mergeWithBackground($processedImage, $id);
            
            self::cleanupTempFiles($tempImagePng);
            
            // Retorna um array com ambas as imagens: a pura do GPT e a final com moldura
            return [
                'gpt' => $processedImage,  // Imagem pura do GPT (image_xxx.jpg)
                'final' => $finalImage    // Imagem final com moldura (output_xxx.png)
            ];
        } catch (\Exception $e) {
            error_log("Error in image generation: " . $e->getMessage());
            throw $e;
        }
    }

    private static function prepareImage(string $fileName): string
    {
        $image = Image::read(file_get_contents($fileName));
        
        if ($image->width() > self::MAX_IMAGE_SIZE || $image->height() > self::MAX_IMAGE_SIZE) {
            $image->scale(
                min(self::MAX_IMAGE_SIZE, self::MAX_IMAGE_SIZE * ($image->width() / $image->height())),
                min(self::MAX_IMAGE_SIZE, self::MAX_IMAGE_SIZE * ($image->height() / $image->width()))
            );
        }

        return self::convertToPng($image);
    }

    private static function convertToPng($image): string
    {
        $tempImage = tempnam(sys_get_temp_dir(), 'openai_input');
        $tempImagePng = $tempImage . '.png';
        $tempJpg = $tempImage . '.jpg';
        
        file_put_contents($tempJpg, (string) $image->encode());
        
        $sourceImage = imagecreatefromstring(file_get_contents($tempJpg));
        $pngImage = self::createTransparentPng($sourceImage);
        
        imagepng($pngImage, $tempImagePng);
        self::cleanupResources([$sourceImage, $pngImage, $tempJpg, $tempImage]);
        
        self::validateImageSize($tempImagePng, $image);
        return $tempImagePng;
    }

    private static function createTransparentPng($sourceImage)
    {
        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);
        $pngImage = imagecreatetruecolor($width, $height);
        
        imagealphablending($pngImage, false);
        imagesavealpha($pngImage, true);
        $transparent = imagecolorallocatealpha($pngImage, 255, 255, 255, 127);
        imagefilledrectangle($pngImage, 0, 0, $width, $height, $transparent);
        imagecopy($pngImage, $sourceImage, 0, 0, 0, 0, $width, $height);
        
        return $pngImage;
    }

    private static function callOpenAIAPI(Client $client, string $apiKey, string $imagePath, string $promptContent): string
    {
        $response = $client->post('https://api.openai.com/v1/images/edits', [
            'headers' => ['Authorization' => 'Bearer ' . $apiKey],
            'multipart' => [
                ['name' => 'image', 'contents' => fopen($imagePath, 'r'), 'filename' => 'input.png'],
                ['name' => 'model', 'contents' => 'gpt-image-1'],
                ['name' => 'prompt', 'contents' => $promptContent],
                //['name' => 'prompt', 'contents' => 'Imagine this person 10 years in the future, highly successful in the technology field. Depict them as a visionary tech leader, surrounded by advanced technology, futuristic devices, and digital innovation. The background should feature an iconic cityscape from either Dubai, the United States, London, or Singapore, symbolizing global success and modern achievement. Maintain the person\'s key facial traits and professional appearance, with a vibrant, inspiring atmosphere.'],
                //['name' => 'prompt', 'contents' => 'Transform this person into a Pixar 3D style programmer character preserving the maximum of the image traits with a programming background containing code snippets on screens, computer monitors, tech gadgets.'],
                ['name' => 'size', 'contents' => '1024x1024']
            ]
        ]);

        $imageData = json_decode($response->getBody(), true);
        if (!isset($imageData["data"][0]["b64_json"])) {
            throw new \Exception("Base64 image not found in API response");
        }
        
        return base64_decode($imageData["data"][0]["b64_json"]);
    }

    private static function processAndSaveOutput(string $imageData): string
    {
        $storagePath = storage_path('app/public');
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $imagemRedimensionada = Image::read($imageData)->scale(616, 616);
        $imageName = "image_" . uniqid() . '.jpg';
        
        if (!Storage::disk('public')->put($imageName, (string) $imagemRedimensionada->encode())) {
            throw new \Exception("Failed to save image using Storage");
        }

        return $imageName;
    }

    private static function mergeWithBackground(string $overlayImageName, int $id): string
    {
        $backgroundPath = resource_path("image/{$id}.png");
        $overlayPath = storage_path('app/public/' . $overlayImageName);
        
        $background = imagecreatefrompng($backgroundPath);
        $overlay = self::createImageFromPath($overlayPath);
        
        $overlayWidth = imagesx($overlay);
        $overlayHeight = imagesy($overlay);
        
        imagealphablending($background, true);
        imagesavealpha($background, true);
        imagecopy($background, $overlay, 232, 270, 0, 0, $overlayWidth, $overlayHeight);
        
        $outputFilename = "output_" . uniqid() . '.png';
        $outputPath = storage_path("app/public/{$outputFilename}");
        imagepng($background, $outputPath);
        
        imagedestroy($background);
        imagedestroy($overlay);
        
        return $outputFilename;
    }

    private static function createImageFromPath(string $path)
    {
        $imageInfo = getimagesize($path);
        if ($imageInfo['mime'] === 'image/jpeg') {
            return imagecreatefromjpeg($path);
        } elseif ($imageInfo['mime'] === 'image/png') {
            return imagecreatefrompng($path);
        }
        throw new \Exception('Unsupported image format. Only JPEG and PNG are allowed.');
    }

    private static function cleanupResources(array $resources): void
    {
        foreach ($resources as $resource) {
            if (is_resource($resource)) {
                imagedestroy($resource);
            } elseif (is_string($resource) && file_exists($resource)) {
                unlink($resource);
            }
        }
    }

    private static function cleanupTempFiles(string $tempImagePng): void
    {
        if (file_exists($tempImagePng)) {
            unlink($tempImagePng);
        }
    }

    private static function validateImageSize(string $imagePath, $image): void
    {
        $fileSize = filesize($imagePath);
        if ($fileSize > self::MAX_FILE_SIZE) {
            $attempts = 0;
            while ($fileSize > self::MAX_FILE_SIZE && $image->width() > 512 && $attempts < 5) {
                $newWidth = (int)($image->width() * 0.75);
                $newHeight = (int)($image->height() * 0.75);
                $image->scale($newWidth, $newHeight);
                
                file_put_contents($imagePath, (string) $image->encode());
                $fileSize = filesize($imagePath);
                $attempts++;
                
                error_log("Attempt {$attempts} - New size: " . ($fileSize / 1024 / 1024) . "MB");
                error_log("New dimensions: " . $image->width() . "x" . $image->height());
            }

            if ($fileSize > self::MAX_FILE_SIZE) {
                throw new \Exception("Image is still too large after resizing: " . ($fileSize / 1024 / 1024) . "MB");
            }
        }

        // Verify PNG format
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $imagePath);
        finfo_close($finfo);

        if ($mimeType !== 'image/png') {
            throw new \Exception("Final file is not PNG (mime type: {$mimeType})");
        }
    }
}