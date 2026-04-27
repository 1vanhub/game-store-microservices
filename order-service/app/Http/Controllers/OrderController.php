<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // Consumer: Create Order (Validates via PlayerService and GameItemService)
    public function store(Request $request)
    {
        $request->validate([
            'player_id' => 'required|integer',
            'game_item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1'
        ]);

        $playerId = $request->player_id;
        $itemId = $request->game_item_id;
        $quantity = $request->quantity;

        try {
            // 1. Get Item Details (to calculate price)
            $itemResponse = Http::get("http://localhost:8002/api/items/{$itemId}");
            if (!$itemResponse->successful()) {
                return response()->json(['message' => 'Game Item not found or Service unreachable'], 404);
            }
            $itemData = $itemResponse->json('data');
            $totalPrice = $itemData['price'] * $quantity;

            // 2. Validate Stock via GameItemService
            $stockResponse = Http::get("http://localhost:8002/api/items/{$itemId}/validate-stock", [
                'quantity' => $quantity
            ]);
            if (!$stockResponse->successful() || !$stockResponse->json('is_available')) {
                return response()->json(['message' => 'Insufficient stock for the requested item'], 400);
            }

            // 3. Validate Player and Balance via PlayerService
            $balanceResponse = Http::get("http://localhost:8001/api/players/{$playerId}/balance");
            if (!$balanceResponse->successful()) {
                return response()->json(['message' => 'Player not found or Service unreachable'], 404);
            }
            
            $walletBalance = $balanceResponse->json('wallet_balance');
            if ($walletBalance < $totalPrice) {
                return response()->json(['message' => 'Insufficient wallet balance'], 400);
            }

            // 4. Proceed to create order
            $order = Order::create([
                'player_id' => $playerId,
                'game_item_id' => $itemId,
                'quantity' => $quantity,
                'total_price' => $totalPrice,
                'status' => 'success'
            ]);

            return response()->json(['message' => 'Order created successfully', 'data' => $order], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to communicate with other services: ' . $e->getMessage()], 500);
        }
    }

    // Provider: Get Player Order History
    public function playerOrders($playerId)
    {
        $orders = Order::where('player_id', $playerId)->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $orders], 200);
    }

    // Provider: Get Sales Recap for Trending Items
    public function salesRecap()
    {
        $sales = Order::select('game_item_id', DB::raw('SUM(quantity) as total_sold'))
            ->where('status', 'success')
            ->groupBy('game_item_id')
            ->orderByDesc('total_sold')
            ->get();

        return response()->json(['data' => $sales], 200);
    }
}
