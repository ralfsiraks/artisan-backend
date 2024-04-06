<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Product;
use App\Models\DiscountCode;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'cart' => 'required|array',
            'discountId' => 'nullable|integer', // Assuming discountId could be nullable
        ]);

        $user = auth()->user();

        // Create a new Stripe Checkout session
        $sessionURL = $this->createStripeCheckoutSession($request->cart, $user, $request->discountId);

        // Return the session ID to the frontend
        return response()->json($sessionURL);
    }

    private function createStripeCheckoutSession(array $cart, User $user, ?int $discountId)
    {
        // Calculate total amount based on products in cart
        $lineItems = $this->makeProductInfo($cart, $discountId);

        // Set your secret key
        Stripe::setApiKey(env('STRIPE_SEC'));

        // Create a Stripe Checkout session
        $session = Session::create([
            'line_items' => [
                $lineItems
            ],
            'mode' => 'payment',
            'success_url' => 'https://kvalifikacija-artisan.vercel.app',
            'cancel_url' => 'https://kvalifikacija-artisan.vercel.app/cart',
            'customer_creation' => 'always',
        ]);

        return $session->url;
    }

    private function makeProductInfo(array $cart, ?int $discountId)
    {
        $lineItems = [];

        $discountPercentage = 0;
        if ($discountId) {
            $discount = DiscountCode::find($discountId);
            if ($discount) {
                $discountPercentage = $discount->amount;
            }
        }

        foreach($cart as $productId) {
            $product = Product::find($productId);
            $discountedPrice = round($product->price * (1 - ($discountPercentage / 100)), 2);

            $lineItem = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $discountedPrice * 100, // Amount in cents
                    'product_data' => [
                        'name' => $product->title,
                        'description' => $product->artist,
                        'images' => [
                            $product->image_url,
                        ]
                    ]
                ],
                'quantity' => 1,
            ];
            
            array_push($lineItems, $lineItem);
        }

        $fee = [
            'price_data' => [
                'currency' => 'usd',
                'unit_amount' => round(1.45 * (1 - ($discountPercentage / 100)), 2) * 100, // Amount in cents
                'product_data' => [
                    'name' => 'Processing fee',
                ]
            ],
            'quantity' => 1,
        ];
        array_push($lineItems, $fee);
         // Calculate total amount based on products in cart
        //  $totalAmount = 1.45;

        //  foreach ($cart as $productId) {
        //      $product = Product::find($productId);
        //      if ($product) {
        //          $totalAmount += $product->price;
        //      }
        //  }
 
        //  // Apply discount if discountId is provided
        //  if ($discountId) {
        //      $discount = DiscountCode::find($discountId);
        //      if ($discount) {
        //         // Calculate discounted total amount
        //         $totalAmount = $totalAmount * (100 - $discount->amount) / 100; // Convert percentage to decimal
        //         $totalAmount = round($totalAmount, 2); // Round to 2 decimal places
        //      }
        //  }
 
         return $lineItems;
    }
}
