<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Illuminate\Support\Str;
use App\Models\DiscountCode;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use App\Models\OrderedProduct;
use Stripe\Exception\SignatureVerificationException;

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
            'success_url' => "https://kvalifikacija-artisan.vercel.app//success?session={CHECKOUT_SESSION_ID}",
            'cancel_url' => 'https://kvalifikacija-artisan.vercel.app//cart',
            'customer_creation' => 'always',
            'customer_email' => $user->email,
            'metadata' => [
                'cart' => json_encode($cart), // Convert array to JSON string
                'user_id' => strval($user->id), // Convert user ID to string
                'discount_id' => strval($discountId), // Convert discount ID to string
            ],
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
         
        return $lineItems;
    }

    public function getSession(Request $request)
    {
        $sessionId = $request->input('session');
        // Set Stripe secret key
        Stripe::setApiKey(env('STRIPE_SEC'));

        // Retrieve session details from Stripe API
        $session = Session::retrieve($sessionId);

        // Retrieve payment intent details
        $paymentIntent = PaymentIntent::retrieve($session->payment_intent);

        // Retrieve payment method details
        $paymentMethod = PaymentMethod::retrieve($paymentIntent->payment_method);

        // Include payment method details in the response
        $session->payment_method_details = $paymentMethod;

        return response()->json(['session' => $session, 'payment_intent' => $paymentIntent], 200);
    }

    public function handleWebhook(Request $request)
{
    // Verify the webhook signature
    try {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $event = Webhook::constructEvent($payload, $sigHeader, env('STRIPE_WEBHOOK_SEC'));
    } catch (SignatureVerificationException $e) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Handle the event
    switch ($event->type) {
        case 'checkout.session.completed':
            $session = $event->data->object;
            $cart = json_decode($session->metadata->cart);
            $userId = $session->metadata->user_id;
            $discountId = $session->metadata->discount_id;

            $order = $this->createOrder($cart, $userId, $discountId);
            break;
        // Handle other event types...
    }

    return response()->json(['success' => true]);
}

private function createOrder(array $cart, int $userId, ?int $discountId): Order
{
    $order = Order::create([
        'user_id' => $userId,
        'created_at' => now(),
        'discount_id' => $discountId
    ]);

    foreach ($cart as $productId) {
        $product = Product::find($productId);
        if ($product) {
            OrderedProduct::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'price' => $product->price
                // Add any additional fields you need for the ordered product
            ]);
        }
    }

    return $order;
}

}
