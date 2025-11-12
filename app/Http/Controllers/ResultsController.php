<?php

namespace App\Http\Controllers;

use App\Models\UserQuestionStatus;
use Illuminate\Http\JsonResponse;

class ResultsController extends Controller
{
    /**
     * Retorna uma lista de UserQuestionStatus somente dos que já finalizaram o questionário
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Busca apenas os UserQuestionStatus que têm vocation preenchido (indicando que finalizaram)
        $finishedUsers = UserQuestionStatus::whereNotNull('vocation')
            ->whereNotNull('image_generated')
            ->orderBy('created_at', 'desc')
            ->get();

        // Formata os dados para o formato esperado pelo frontend
        $results = $finishedUsers->map(function ($userStatus) {
            // Gera a URL completa da imagem pura do GPT (sem moldura)
            $imageUrl = $userStatus->image_gpt 
                ? asset('storage/' . $userStatus->image_gpt)
                : '';

            return [
                'id' => (string) $userStatus->id,
                'userName' => $userStatus->name ?? '',
                'userPhoto' => $imageUrl,
                'profession' => $userStatus->vocation ?? '',
            ];
        });

        return response()->json($results)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}

