<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Hamcrest\Arrays\IsArray;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function getCart(Request $request) {
        $productArr = $request->header('cart');
        $productIds = json_decode($productArr, true);
        $products = Product::whereIn('id', $productIds)->get();
        return response()->json($products);
        // komentars
    }

    public function getCatalog(Request $request, string $category) {
        if($category === 'all') {
             $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                     ->select('products.*', 'categories.title as category_title')
                     ->paginate(8, ['*']);
        } else {
            $products = Product::join('categories', 'products.category_id', '=', 'categories.id')
                ->select('products.*', 'categories.title as category_title')
                ->where('categories.title', str_replace('_', ' ', $category))
                ->paginate(40, ['*']);
        }
        return response()->json($products);
    }

    public function getProduct(Request $request) {
        $id = $request->header('id');
        $product = Product::select('products.*', 'categories.title as category_title')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.id', $id)
            ->first();
        if ($product) {
            return response()->json($product, 200);
        } else {
            return response()->json('Product not found!', 400);
        }
    }
}
