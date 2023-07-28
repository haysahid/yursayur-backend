<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Order;
use App\Models\Store;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payment_status = $request->input('payment_status');
        $limit = $request->input('limit', 10);

        $user = Auth::user();
        $orders = Order::query();

        if ($payment_status) {
            $orders->where('orders.payment_status', $payment_status);
        }

        $orders->where('user_id', $user->id)->with('order_items')->latest();

        return ResponseFormatter::success(
            $orders->paginate($limit),
            'Daftar pesanan berhasil ditemukan.',
            200
        );
    }

    /**
     * Order a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'address' => 'required|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            // Create order
            $address = $request->input('address');
            $shipping_costs = 10000;
            $notes = $request->input('notes');
            $payment_status = 'Pending';

            $order = Order::create([
                'user_id' => $user->id,
                'address' => $address,
                'shipping_costs' => $shipping_costs,
                'notes' => $notes,
                'payment_status' => $payment_status,
            ]);

            // Add order items
            $cart_items = CartItem::where([
                ['user_id', '=', $user->id],
                ['is_selected', '=', 1],
            ])->get();

            foreach ($cart_items as $cart_item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart_item->product_id,
                    'quantity' => $cart_item->quantity,
                ]);
            }

            // Delete cart item
            $cart_items->delete();

            // Count sub total
            $count_sub_total = CartItem::join('products', 'cart_items.product_id', '=', 'products.id')
                ->select(DB::raw('sum(cart_items.quantity * products.selling_price) as sub_total'))
                ->where([
                    ['user_id', '=', $user->id],
                    ['is_selected', '=', 1],
                ])->first();

            $order = Order::with(['order_items'])->find($order->id);

            return ResponseFormatter::success([
                'order' => $order,
                'sub_total' => (int) $count_sub_total->sub_total + $order->shipping_costs,
            ], 'Pesanan berhasil ditambahkan.', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Pesanan gagal ditambahkan.' . $error, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with('order_items')->find($id);

        if (!$order) {
            return ResponseFormatter::error('Pesanan tidak ditemukan.', 404);
        }

        return ResponseFormatter::success([
            'order' => $order,
        ], 'Pesanan berhasil ditemukan.', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return ResponseFormatter::error('Pesanan tidak ditemukan.', 404);
        }

        $request->validate([
            'payment_status' => ['required', 'string', Rule::in(['Success', 'Pending', 'Failed'])],
        ]);

        try {
            $order->update([
                'payment_status' => $request->input('payment_status'),
            ]);

            $order = Order::with('order_items')->find($id);

            return ResponseFormatter::success([
                'order' => $order,
            ], 'Pesanan berhasil diubah.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Pesanan gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $order = Order::find($id);

        // if (!$order) {
        //     return ResponseFormatter::error('Pesanan tidak ditemukan.', 404);
        // }

        // $order->delete();

        // return ResponseFormatter::success(
        //     $order->id,
        //     'Pesanan berhasil dihapus.',
        //     200
        // );
    }
}
