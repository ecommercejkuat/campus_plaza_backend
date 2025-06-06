<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        \Log::info('Orders endpoint hit');
        $orders = Auth::user()->isSuperUser()
            ? Order::with(['buyer', 'orderItems.product'])->get()
            : Order::with(['buyer', 'orderItems.product'])->where('buyer_id', Auth::id())->get();
        \Log::info('Fetched orders: ' . $orders->toJson());
        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        \Log::info('Store order endpoint hit', $request->all());

        if (Auth::user()->role !== 'buyer') {
            return response()->json(['message' => 'Only buyers can create orders'], 403);
        }

        $totalPrice = 0;
        $items = $request->items;

        // Calculate total price and validate product availability
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            if (!$product->is_approved) {
                return response()->json(['message' => 'Product not approved'], 422);
            }
            if ($product->quantity < $item['quantity']) {
                return response()->json(['message' => 'Insufficient stock for product: ' . $product->title], 422);
            }
            $totalPrice += $product->price * $item['quantity'];
        }

        // Create order and order items in a transaction
        return DB::transaction(function () use ($items, $totalPrice) {
            $order = Order::create([
                'buyer_id' => Auth::id(),
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
                // Update product stock
                $product->decrement('quantity', $item['quantity']);
            }

            return response()->json([
                'order' => $order->load('orderItems.product'),
                'message' => 'Order created successfully'
            ], 201);
        });
    }

    public function updateStatus(Request $request, Order $order)
    {
        \Log::info('Update order status endpoint hit', ['order_id' => $order->id]);

        if (!Auth::user()->isSuperUser()) {
            return response()->json(['message' => 'Only superusers can update order status'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,paid,shipped,delivered',
        ]);

        $order->update(['status' => $request->status]);

        return response()->json([
            'order' => $order->load('orderItems.product'),
            'message' => 'Order status updated'
        ]);
    }
}