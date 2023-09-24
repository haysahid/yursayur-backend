<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(String $productId, Request $request)
    {
        // Check product availability
        $product = Product::find($productId);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $limit = $request->input('limit', 10);

        $reviews = Review::query();

        $reviews->where('product_id', $productId)->with('user')
            ->select('reviews.*')->latest();

        return ResponseFormatter::success(
            $reviews->paginate($limit),
            'Daftar tanggapan berhasil ditemukan.',
            200
        );
    }

    /**
     * Review a newly created resource in storage.
     */
    public function store(String $productId, Request $request)
    {
        $request->validate([
            'order_item_id' => 'required',
            'description' => 'nullable|string|max:255',
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
        ]);

        // Check product availability
        $product = Product::find($productId);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $user = Auth::user();

        $order_item_id = $request->input('order_item_id');

        try {
            $review = Review::create([
                'product_id' => $productId,
                'user_id' => $user->id,
                'description' => $request->input('description'),
                'rating' => $request->input('rating'),
            ]);

            // Update order item
            OrderItem::find($order_item_id)->update([
                'is_reviewed' => 1,
            ]);

            $review = Review::with('user')->find($review->id);

            return ResponseFormatter::success([
                'review' => $review,
            ], 'Tanggapan berhasil ditambahkan.', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Tanggapan gagal ditambahkan.', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(String $productId, string $id)
    {
        // Check product availability
        $product = Product::find($productId);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $productId)->with('user')->find($id);

        if (!$review) {
            return ResponseFormatter::error('Tanggapan tidak ditemukan.', 404);
        }

        return ResponseFormatter::success([
            'review' => $review,
        ], 'Tanggapan berhasil ditemukan.', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(String $productId, Request $request, string $id)
    {
        $request->validate([
            'product_id' => 'nullable',
            'description' => 'nullable|string|max:255',
            'rating' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
        ]);

        // Check product availability
        $product = Product::find($productId);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $productId)->find($id);

        if (!$review) {
            return ResponseFormatter::error('Tanggapan tidak ditemukan.', 404);
        }

        try {
            $review->update($request->all());

            $review = Review::with('user')->find($review->id);

            return ResponseFormatter::success([
                'review' => $review,
            ], 'Data tanggapan berhasil diubah.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Tanggapan gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(String $productId, string $id)
    {
        // Check product availability
        $product = Product::find($productId);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $productId)->find($id);

        if (!$review) {
            return ResponseFormatter::error('Tanggapan tidak ditemukan.', 404);
        }

        $review->delete();

        return ResponseFormatter::success(
            $review->id,
            'Tanggapan berhasil dihapus.',
            200
        );
    }
}
