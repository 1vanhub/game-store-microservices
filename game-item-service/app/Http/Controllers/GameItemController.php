<?php

namespace App\Http\Controllers;

use App\Models\GameItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameItemController extends Controller
{
    // Provider: Get item details
    public function show($id)
    {
        $item = GameItem::find($id);
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }
        return response()->json(['data' => $item], 200);
    }

    // Provider: Validate stock
    public function validateStock($id, Request $request)
    {
        $quantity = $request->query('quantity', 1);
        $item = GameItem::find($id);
        
        if (!$item) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        if ($item->stock >= $quantity) {
            return response()->json(['is_available' => true, 'stock' => $item->stock], 200);
        }

        return response()->json(['is_available' => false, 'stock' => $item->stock], 400);
    }

    // Consumer: Get trending items from OrderService (Port 8003)
    public function trendingItems()
    {
        try {
            $response = Http::get("http://localhost:8003/api/orders/recap");
            
            if ($response->successful()) {
                $salesData = $response->json('data');
                
                // Fetch details for trending items
                $trendingItems = [];
                foreach ($salesData as $sale) {
                    $item = GameItem::find($sale['game_item_id']);
                    if ($item) {
                        $trendingItems[] = [
                            'item' => $item,
                            'total_sold' => $sale['total_sold']
                        ];
                    }
                }

                return response()->json(['data' => $trendingItems], 200);
            }
            
            return response()->json(['message' => 'Failed to fetch sales data'], $response->status());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Order Service is unreachable'], 500);
        }
    }
}
