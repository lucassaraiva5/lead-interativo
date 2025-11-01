<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppConnectionController extends Controller
{
    private $whatsappServerUrl;

    public function __construct()
    {
        $this->whatsappServerUrl = env('WHATSAPP_SERVER_URL', 'http://localhost:3000');
    }

    public function index()
    {
        return view('whatsapp-connection');
    }

    public function sendMessage(Request $request)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($this->whatsappServerUrl . '/send-message', [
                'json' => [
                    'number' => $request->number,
                    'message' => $request->message
                ]
            ]);

            return response()->json(json_decode($response->getBody()));
        } catch (\Exception $e) {
            Log::error('Error sending message: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending message'
            ], 500);
        }
    }

    public function checkStatus()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($this->whatsappServerUrl . '/status');

            return response()->json(json_decode($response->getBody()));
        } catch (\Exception $e) {
            Log::error('Error checking status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking status'
            ], 500);
        }
    }

    public function logout()
    {
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->post($this->whatsappServerUrl . '/logout');

            return response()->json(json_decode($response->getBody()));
        } catch (\Exception $e) {
            Log::error('Error logging out: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error logging out'
            ], 500);
        }
    }
}