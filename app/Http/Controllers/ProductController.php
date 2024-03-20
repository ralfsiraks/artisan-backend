<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

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
        $sortBy = $request->input('sort_by', 'id'); // Default sorting column
        $sortOrder = $request->input('sort_order', 'asc'); // Default sorting order
    
        // Pagination parameters
        $perPage = 12; // Default number of items per page
    
        if($category === 'all') {
            $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
                    ->select('products.*', 'categories.title as category_title');
        } else {
            $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
                    ->select('products.*', 'categories.title as category_title')
                    ->where('categories.title', str_replace('_', ' ', $category));
        }
    
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
    
        // Paginate the results
        $products = $query->paginate($perPage, ['*'], 'page', $request->input('page'));
    
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
