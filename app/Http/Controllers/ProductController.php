<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        \Log::info('Products endpoint hit');
        $products = Product::with('user')->get();
        \Log::info('Fetched products: ' . $products->toJson());
        return response()->json($products);
    }

    public function store(StoreProductRequest $request)
    {
        \Log::info('Store product endpoint hit', $request->all());

        if (Auth::user()->role !== 'seller') {
            return response()->json(['message' => 'Only sellers can create products'], 403);
        }

        $product = Product::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'image_path' => $request->image_path,
            'is_approved' => false,
        ]);

        return response()->json([
            'product' => $product,
            'message' => 'Product created, pending approval'
        ], 201);
    }

    public function approve(Request $request, Product $product)
    {
        \Log::info('Approve product endpoint hit', ['product_id' => $product->id]);

        if (Auth::user()->role !== 'superuser') {
            return response()->json(['message' => 'Only super users can approve products'], 403);
        }

        $product->update(['is_approved' => true]);

        return response()->json([
            'product' => $product,
            'message' => 'Product approved'
        ]);
    }
}