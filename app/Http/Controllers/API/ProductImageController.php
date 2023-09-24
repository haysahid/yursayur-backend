<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(String $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }
        
        $productImages = ProductImage::where('product_id', $productId)->get();

        return ResponseFormatter::success([
            'total' => count($productImages),
            'product_images' => $productImages,
        ], 'Gambar produk berhasil ditemukan.', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, String $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $request->validate([
            'product_images' => 'nullable|array',
            'product_images.*' => 'nullable|file',
        ]);

        try {
            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');

                $productImages = self::addProductImages($productId, $files);
            }

            return ResponseFormatter::success([
                'total' => count($productImages),
                'product_images' => $productImages,
            ], 'Gambar produk berhasil ditambahkan.', 201);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Gambar produk gagal ditambahkan.' . $error, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $productId, string $id)
    {
        $productImage = ProductImage::where('product_id', $productId)->where('id', $id)->first();

        if (!$productImage) {
            return ResponseFormatter::error('Produk atau gambar produk tidak ditemukan.', 404);
        }

        $request->validate([
            'product_image' => 'required|file',
        ]);

        try {
            if ($request->hasFile('product_image')) {
                $file = $request->file('product_image');
                $productImage = self::updateProductImage($id, $file);
            }

            return ResponseFormatter::success([
                'product_image' => $productImage,
            ], 'Gambar produk berhasil diubah.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Gambar produk gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $productId, string $id)
    {
        $productImage = ProductImage::where('product_id', $productId)->where('id', $id)->first();

        if (!$productImage) {
            return ResponseFormatter::error('Produk atau gambar produk tidak ditemukan.', 404);
        }

        // Delete old image
        if ($productImage->image) {
            Storage::delete($productImage->image);
        }

        $productImage->delete();

        return ResponseFormatter::success(
            $productImage->id,
            'Gambar produk berhasil dihapus.',
            200
        );
    }

    public static function addProductImages($productId, $files)
    {
        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->first();

        foreach ($files as $file) {
            if (!$file) continue;

            $image_path = $file->store('store/' . $store->id . '/product');

            ProductImage::create([
                'image' => $image_path,
                'product_id' => $productId,
            ]);
        }

        $productImages = ProductImage::where('product_id', $productId)->get();

        return $productImages;
    }

    public static function updateProductImage($productImageId, $file)
    {
        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->first();

        $productImage = ProductImage::find($productImageId);

        // Delete old image
        if ($productImage->image) {
            Storage::delete($productImage->image);
        }

        // Store image 
        $image_path = $file->store('store/' . $store->id . '/product');

        // Add to database
        $productImage->update([
            'image' => $image_path,
        ]);

        $productImage = ProductImage::find($productImageId);

        return $productImage;
    }
}
