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
    }

    public function getCatalog(Request $request) {
        $query = $this->buildQuery($request);
        $searchTerm = $request->input('search');
    
        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('products.title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('products.artist', 'like', '%' . $searchTerm . '%');
            });
        }
    
        $query = $this->applyFilters($request, $query);
    
        $products = $this->paginateResults($request, $query);
    
        return response()->json($products);
    }
    
    public function buildQuery(Request $request) {
        $sort = $request->input('sort', 'id_desc');
        $sortParts = explode('_', $sort);
        $sortBy = $sortParts[0];
        $sortOrder = $sortParts[1];
        $category = $request->input('category', 'all');
    
        $query = Product::join('categories', 'products.category_id', '=', 'categories.id')
                        ->select('products.*', 'categories.title as category_title');
    
        if ($category !== 'all') {
            $query->where('categories.title', str_replace('_', ' ', $category));
        }
    
        // Apply sorting
        $query->orderBy($sortBy, $sortOrder);
    
        return $query;
    }

    public function applyFilters(Request $request, $query) {
        $filters = [
            'height' => $request->input('h'),
            'width' => $request->input('w')
        ];
    
        foreach ($filters as $attribute => $filter) {
            if ($filter) {
                switch ($filter) {
                    case 'S':
                        $query->where("products.$attribute", '<=', 800);
                        break;
                    case 'M':
                        $query->whereBetween("products.$attribute", [1200, 2400]);
                        break;
                    case 'L':
                        $query->whereBetween("products.$attribute", [2400, 3600]);
                        break;
                    case 'XL':
                        $query->where("products.$attribute", '>=', 3600);
                        break;
                }
            }
        }
    
        // Price Range Filter
        $minPrice = $request->input('minP');
        $maxPrice = $request->input('maxP');
    
        if ($minPrice !== null && $maxPrice !== null) {
            $query->whereBetween('products.price', [$minPrice, $maxPrice]);
        } else {
            if ($minPrice !== null) {
                $query->where('products.price', '>=', $minPrice);
            }
            if ($maxPrice !== null) {
                $query->where('products.price', '<=', $maxPrice);
            }
        }
    
        return $query;
    }
    
    
    public function paginateResults(Request $request, $query) {
        $perPage = 12;
        return $query->paginate($perPage, ['*'], 'page', $request->input('page'));
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
