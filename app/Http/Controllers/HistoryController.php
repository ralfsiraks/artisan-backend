<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function getOrderHistory(Request $request) {
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

    //  Add a discount_amount property 
     $orders->transform(function ($order) {
        if ($order->discount_code) {
            $order->discount_amount = $order->discount_code->amount;
        } else {
            $order->discount_amount = 0;
        }
        return $order;
    });
 
     return response()->json($orders);
    }

    public function getOrder(Request $request, int $orderId) {
        $order = Order::with('ordered_products', 'discount_code')->find($orderId);
        

        $order->ordered_products->each(function ($orderedProduct) {
            // Keep the price from the ordered_products table
            $orderedProduct->product->price = $orderedProduct->price;
        });
        return response()->json($order);
    }
}
