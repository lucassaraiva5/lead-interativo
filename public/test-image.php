<?php

require __DIR__.'/../vendor/autoload.php';

use App\Services\AIServiceIntegration;
use Illuminate\Support\Facades\Storage;

// Initialize Laravel's App container
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Image Transformation Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container { 
            margin: 20px 0;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .image-container { 
            margin: 20px 0; 
            text-align: center;
        }
        img { 
            max-width: 500px; 
            border: 1px solid #ccc; 
            border-radius: 5px;
            margin: 10px 0;
        }
        input[type="text"] {
            width: 80%;
            padding: 8px;
            margin: 5px 0;
        }
        input[type="submit"] {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h1>Image Transformation Test</h1>
    
    <div class="form-container">
        <form method="POST" action="">
            <div>
                <label for="imageUrl">Enter Image URL:</label><br>
                <input type="text" id="imageUrl" name="imageUrl" value="<?php echo isset($_POST['imageUrl']) ? htmlspecialchars($_POST['imageUrl']) : ''; ?>" placeholder="https://example.com/image.jpg">
            </div>
            <div>
                <label for="backgroundId">Background ID:</label><br>
                <input type="text" id="backgroundId" name="backgroundId" value="<?php echo isset($_POST['backgroundId']) ? htmlspecialchars($_POST['backgroundId']) : '1'; ?>" placeholder="1">
            </div>
            <div style="margin-top: 10px;">
                <input type="submit" value="Transform Image">
            </div>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['imageUrl'])) {
        try {
            // Download the image from URL
            $tempImage = tempnam(sys_get_temp_dir(), 'test_image');
            file_put_contents($tempImage, file_get_contents($_POST['imageUrl']));

            // The ID for your background image
            $backgroundId = isset($_POST['backgroundId']) ? intval($_POST['backgroundId']) : 1;

            // Generate the Pixar-style image
            $outputFilename = AIServiceIntegration::generateImage($tempImage, $backgroundId);

            // Clean up the temporary file
            unlink($tempImage);

            // Get the full URL of the generated image
            $imageUrl = asset('storage/' . $outputFilename);
            ?>
            <div class="image-container">
                <h2>Original Image:</h2>
                <img src="<?php echo htmlspecialchars($_POST['imageUrl']); ?>" alt="Original Image">
            </div>

            <div class="image-container">
                <h2>Generated Pixar-style Image:</h2>
                <img src="<?php echo htmlspecialchars($imageUrl); ?>" alt="Generated Image">
            </div>
            <?php
        } catch (Exception $e) {
            echo '<div class="error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
    ?>
</body>
</html>