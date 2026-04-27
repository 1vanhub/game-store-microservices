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

        // Accept optional direct data from frontend
        $gameTitle = $request->input('game_title', null);
        $itemName = $request->input('item_name', null);
        $playerName = $request->input('player_name', null);
        $directPrice = $request->input('total_price', null);

        $totalPrice = 0;

        try {
            // 1. Try to get Item Details from GameItemService
            $itemResponse = Http::timeout(3)->get("http://localhost:8002/api/items/{$itemId}");
            if ($itemResponse->successful()) {
                $itemData = $itemResponse->json('data');
                $totalPrice = $itemData['price'] * $quantity;
                if (!$itemName) $itemName = $itemData['name'];

                // 2. Validate Stock via GameItemService
                $stockResponse = Http::timeout(3)->get("http://localhost:8002/api/items/{$itemId}/validate-stock", [
                    'quantity' => $quantity
                ]);
                if ($stockResponse->successful() && !$stockResponse->json('is_available')) {
                    return response()->json(['message' => 'Insufficient stock for the requested item'], 400);
                }
            } elseif ($directPrice) {
                // Item not in DB but frontend sent the price directly
                $totalPrice = $directPrice;
            } else {
                return response()->json(['message' => 'Game Item not found and no price provided'], 404);
            }

            // 3. Try to validate Player via PlayerService
            $balanceResponse = Http::timeout(3)->get("http://localhost:8001/api/players/{$playerId}/balance");
            if ($balanceResponse->successful()) {
                $walletBalance = $balanceResponse->json('wallet_balance');
                if (!$playerName) {
                    $profileResponse = Http::timeout(3)->get("http://localhost:8001/api/players/{$playerId}/profile");
                    if ($profileResponse->successful()) {
                        $playerName = $profileResponse->json('data.name');
                    }
                }
                if ($walletBalance < $totalPrice) {
                    return response()->json(['message' => 'Insufficient wallet balance'], 400);
                }
            }
            // If PlayerService is unreachable, continue with the order using frontend data

        } catch (\Exception $e) {
            // Services unreachable - use direct price from frontend if available
            if ($directPrice) {
                $totalPrice = $directPrice;
            } else {
                return response()->json(['message' => 'Failed to communicate with services: ' . $e->getMessage()], 500);
            }
        }

        // 4. Create order with all available data
        $order = Order::create([
            'player_id' => $playerId,
            'player_name' => $playerName,
            'game_title' => $gameTitle,
            'game_item_id' => $itemId,
            'item_name' => $itemName,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'status' => 'success'
        ]);

        return response()->json(['message' => 'Order created successfully', 'data' => $order], 201);
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
