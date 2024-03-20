<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function orderHistory(Request $request) {
        $user = auth()->user();

         $orders = Order::with('ordered_products.product')
         ->where('user_id', $user->id)
         ->get();
 
     // Update the price of each product in the order
     $orders->each(function ($order) {
         $order->ordered_products->each(function ($orderedProduct) {
             $orderedProduct->product->price = $orderedProduct->price;
         });
     });

     $orders->transform(function ($order) {
        if ($order->discount_code) {
            $order->discount_amount = $order->discount_code->amount;
        } else {
            $order->discount_amount = 0;
        }
        return $order;
    });
 
     // Return the user's order history
     return response()->json($orders);
    }
}
