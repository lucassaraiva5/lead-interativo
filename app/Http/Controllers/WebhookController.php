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
        // Retorna uma resposta para o UltraMsg
        return response("", 200);
    }

}
