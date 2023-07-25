<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(String $product_id, Request $request)
    {
        // Check product availability
        $product = Product::find($product_id);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $limit = $request->input('limit', 10);

        $reviews = Review::query();

        $reviews->where('product_id', $product_id)->with('user')
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
    public function store(String $product_id, Request $request)
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'rating' => ['required', 'integer', Rule::in([1, 2, 3, 4, 5])],
        ]);

        // Check product availability
        $product = Product::find($product_id);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $user = Auth::user();

        try {
            $review = Review::create([
                'product_id' => $product_id,
                'user_id' => $user->id,
                'description' => $request->input('description'),
                'rating' => $request->input('rating'),
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
    public function show(String $product_id, string $id)
    {
        // Check product availability
        $product = Product::find($product_id);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $product_id)->with('user')->find($id);

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
    public function update(String $product_id, Request $request, string $id)
    {
        $request->validate([
            'product_id' => 'nullable',
            'description' => 'nullable|string|max:255',
            'rating' => ['nullable', 'integer', Rule::in([1, 2, 3, 4, 5])],
        ]);

        // Check product availability
        $product = Product::find($product_id);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $product_id)->find($id);

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
    public function destroy(String $product_id, string $id)
    {
        // Check product availability
        $product = Product::find($product_id);
        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $review = Review::where('product_id', $product_id)->find($id);

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
