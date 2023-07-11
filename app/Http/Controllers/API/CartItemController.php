<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $cart_items = CartItem::query();

        // TODO: Group cart item by store

        // $cart_items
        // ->join('products', 'cart_items.id', '=', 'products.id')
        // ->join('stores', 'products.store_id', '=', 'stores.id')
        // ->

        // $sub_totals = $cart_items
        //     ->join('products', 'cart_items.product_id', '=', 'products.id')
        //     ->select(DB::raw('sum(cart_items.quantity * products.selling_price) as sub_total'))
        //     ->groupBy('products.store_id')
        //     ->orderBy('sub_total', 'desc')
        //     ->where('user_id', $user->id)->get();

        $cart_items->where('user_id', $user->id)->latest();

        return ResponseFormatter::success(
            [
                'cart_items' => $cart_items->get(),
            ],
            'Daftar produk berhasil ditemukan.',
            200
        );
    }

    /**
     * Product a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'quantity' => 'required|numeric',
        ]);

        $user = Auth::user();

        try {
            $cart_item = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $request->input('product_id'),
                'quantity' => $request->input('quantity'),
                'is_selected' => 1,
            ]);

            $cart_item = CartItem::with('product')->find($cart_item->id);

            return ResponseFormatter::success([
                'cart_item' => $cart_item,
            ], 'Produk berhasil ditambahkan ke keranjang.', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Produk gagal ditambahkan ke keranjang.' . $error, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cart_item = CartItem::find($id);

        if (!$cart_item) {
            return ResponseFormatter::error('Item keranjang tidak ditemukan.', 404);
        }

        $request->validate([
            'quantity' => 'nullable|numeric',
            'is_selected' => 'nullable|boolean',
        ]);

        try {
            $cart_item->update($request->all());

            $cart_item = CartItem::with('product')->find($id);

            return ResponseFormatter::success(
                [
                    'cart_item' => $cart_item,
                ],
                'Item keranjang berhasil diubah.',
                200
            );
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Item keranjang gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cart_item = CartItem::find($id);

        if (!$cart_item) {
            return ResponseFormatter::error('Item keranjang tidak ditemukan.', 404);
        }

        $cart_item->delete();

        return ResponseFormatter::success(
            $cart_item->id,
            'Item keranjang berhasil dihapus.',
            200
        );
    }
}
