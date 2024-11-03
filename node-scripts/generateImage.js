// Importa e configura o dotenv
import dotenv from 'dotenv';
dotenv.config();

import OpenAI from 'openai';

// Configuração da API com a chave
const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

async function getImageDescription(imageUrl) {
    const response = await openai.chat.completions.create({
        model: "gpt-4o",
        messages: [
            {
                role: "user",
                content: [
                    { 
                        type: "text", 
                        text: "Don't need to tell who is the person on the image, just all appearence details and age to I can create a character from a book and ignoring the background and just respond with those details" 
                    },
                    {
                        type: "image_url",
                        image_url: {
                        "url": imageUrl,
                        },
                    },
                ],
            },
        ],
    });
    return response.choices[0].message.content;
}

async function main() {
    
    let imageDescription = null;

    for (let index = 0; index < 5; index++) {
        imageDescription = await getImageDescription("https://assets2.cbsnewsstatic.com/hub/i/r/2019/01/26/1c2d45b1-af86-4091-bef3-a19e86155131/thumbnail/1280x720/56ca4f21d138749a63a89fd5f1ca09a5/0126-satmo-wikipediaeditor-barnett-1767717-640x360.jpg?v=379420b9063a2aadbcd559df18e2d1ae");
        if(imageDescription.indexOf("I'm sorry") === -1) {
            break;
        }
    }    
    const image = await openai.images.generate(
        { 
            model: "dall-e-3",
            prompt: "Crie um personagem pixar 3d inspirado na seguinte descrição: " + imageDescription + ". O fundo da imagem deve representar que ele é um programador backend."
        }
    );


    console.log(JSON.stringify(image.data));
}
main();