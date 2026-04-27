<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PlayerController extends Controller
{
    // Provider: Get profile
    public function profile($id)
    {
        $player = Player::find($id);
        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }
        return response()->json(['data' => $player], 200);
    }

    // Provider: Check balance
    public function checkBalance($id)
    {
        $player = Player::find($id);
        if (!$player) {
            return response()->json(['message' => 'Player not found'], 404);
        }
        return response()->json(['wallet_balance' => $player->wallet_balance], 200);
    }

    // Consumer: Get transaction history from OrderService (Port 8003)
    public function transactionHistory($id)
    {
        try {
            $response = Http::get("http://localhost:8003/api/orders/player/{$id}");
            
            if ($response->successful()) {
                return response()->json([
                    'player_id' => $id,
                    'transactions' => $response->json('data')
                ], 200);
            }
            
            return response()->json(['message' => 'Failed to fetch transaction history'], $response->status());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order Service is unreachable'], 500);
        }
    }
}
