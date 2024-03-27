<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getCart(Request $request) {
        return response()->json(["test"=>"test"]);
        // $productArr = $request->header('cart');
        // $productIds = json_decode($productArr, true);
        // $products = Product::whereIn('id', $productIds)->get();
        // return response()->json($products);
    }

    // public function getCatalog(Request $request) {
    //     $query = $this->buildQuery($request);
    //     $searchTerm = $request->input('search');
        
    //     if ($searchTerm) {
    //         $query->where(function($q) use ($searchTerm) {
    //             $q->where('products.title', 'like', '%' . $searchTerm . '%')
    //               ->orWhere('products.artist', 'like', '%' . $searchTerm . '%');
    //         });
    //     }
        
    //     $products = $this->paginateResults($request, $query);
        
    //     return response()->json($products);
    // }
    
    // public function buildQuery(Request $request) {
    //     $sortBy = $request->input('sort_by', 'id');
    //     $sortOrder = $request->input('sort_order', 'asc');
    //     $category = $request->input('category', 'all');
    
    //     $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
    //                     ->select('products.*', 'categories.title as category_title');
    
    //     if ($category !== 'all') {
    //         $query->where('categories.title', str_replace('_', ' ', $category));
    //     }
    
    //     // Apply sorting
    //     $query->orderBy($sortBy, $sortOrder);
    
    //     return $query;
    // }
    
    // public function paginateResults(Request $request, $query) {
    //     $perPage = 12;
    //     return $query->paginate($perPage, ['*'], 'page', $request->input('page'));
    // }
    

    
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
