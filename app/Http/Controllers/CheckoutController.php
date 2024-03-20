<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\OrderedProduct;

class CheckoutController extends Controller
{
    public function checkout(Request $request) {
        $cart = $request->input('cart');
        $discountId = $request->input('discountId');
        $discountId = $discountId != 0 ? $discountId : null;
        $userId = auth()->user()->id;

        $order = Order::create([
            'user_id' => $userId,
            'created_at' => now(),
            'discount_id' => $discountId

        ]);

        foreach ($cart as $productId) {
            $product = Product::find($productId);
            if($product) {
            OrderedProduct::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'price' => $product->price
                // Add any additional fields you need for the ordered product
            ]);
            }
            
        }
        return response()->json($discountId);
    }
}
