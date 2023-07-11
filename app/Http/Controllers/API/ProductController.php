<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Tag;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProductTag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $store_id = $request->input('store_id');
        $category = $request->input('category');
        $search = $request->input('search');
        $limit = $request->input('limit', 10);

        $products = Product::query();

        if ($store_id) {
            $products->where('products.store_id', $store_id);
        }

        if ($category) {
            $products->where('products.category', $category);
        }

        if ($search) {
            $products
                ->where('products.name', 'like', '%' . $search . '%')
                ->orWhere('products.description', 'like', '%' . $search . '%');
        }

        $products->with(['store', 'product_images', 'tags'])->select('products.*')->latest();

        return ResponseFormatter::success(
            $products->paginate($limit),
            'Daftar produk berhasil ditemukan.',
            200
        );
    }

    /**
     * Product a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $store = Store::where('user_id', $user->id)->first();

        // Store availability check
        if (!$store) {
            return ResponseFormatter::error('Anda belum memiliki toko.', 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'capital_price' => 'required|integer',
            'selling_price' => 'required|integer',
            'stock' => 'required|integer',
            'unit' => 'required|string|max:30',
            'category' => ['required', 'string', Rule::in(['Sayur', 'Buah'])],

            // product_images
            'product_images' => 'nullable|array',
            'product_images.*' => 'nullable|file',

            // tags
            'tags' => 'nullable|string|max:255',
        ]);

        try {
            $product = Product::create([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'capital_price' => $request->input('capital_price'),
                'selling_price' => $request->input('selling_price'),
                'stock' => $request->input('stock'),
                'unit' => strtolower($request->input('unit')),
                'category' => $request->input('category'),
                'store_id' => $store->id,
            ]);

            // Product images
            if ($request->hasFile('product_images')) {
                $files = $request->file('product_images');

                foreach ($files as $file) {
                    $image_path = $file->store('store/' . $store->id . '/product');

                    ProductImage::create([
                        'image' => $image_path,
                        'product_id' => $product->id,
                    ]);
                }
            }

            // Tags
            $tags = explode(',', strtolower($request->input('tags')));

            foreach ($tags as $item) {
                // Tag
                $item = trim($item);
                $tag = Tag::query();

                if (sizeof(Tag::where('tag', $item)->get()) == 0) {
                    $tag = $tag->create([
                        'tag' => $item,
                    ]);
                } else {
                    $tag = $tag->where('tag', $item)->first();
                }

                // Product Tag
                ProductTag::create([
                    'product_id' => $product->id,
                    'tag_id' => $tag->id,
                ]);
            }

            $product = Product::with(['store', 'product_images', 'tags'])->find($product->id);

            return ResponseFormatter::success([
                'product' => $product,
            ], 'Produk berhasil ditambahkan.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Produk gagal ditambahkan.' . $error, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with(['store', 'product_images', 'tags'])->find($id);

        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        return ResponseFormatter::success([
            'product' => $product,
        ], 'Produk berhasil ditemukan.', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $store = Store::find($product->store_id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:255',
            'capital_price' => 'nullable|integer',
            'selling_price' => 'nullable|integer',
            'stock' => 'nullable|integer',
            'unit' => 'nullable|string|max:30',
            'category' => ['nullable', 'string', Rule::in(['Sayur', 'Buah'])],

            // tags
            'tags' => 'nullable|string|max:255',
        ]);

        try {
            // Update product
            $product->update($request->all());

            // Update tags
            $previousTags = ProductTag::where('product_tags.product_id', $product->id);
            $previousTags->delete();

            $tags = explode(',', strtolower($request->input('tags')));

            foreach ($tags as $item) {
                // Tag
                $item = trim($item);
                $tag = Tag::query();

                if (sizeof(Tag::where('tag', $item)->get()) == 0) {
                    $tag = $tag->create([
                        'tag' => $item,
                    ]);
                } else {
                    $tag = $tag->where('tag', $item)->first();
                }

                // Product Tag
                ProductTag::create([
                    'product_id' => $product->id,
                    'tag_id' => $tag->id,
                ]);
            }

            $product = Product::with(['store', 'product_images', 'tags'])->find($product->id);

            return ResponseFormatter::success([
                'product' => $product,
            ], 'Produk berhasil diubah.', 200);
        } catch (Exception $error) {
            return ResponseFormatter::error('Terjadi kesalahan. Produk gagal diubah.' . $error, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return ResponseFormatter::error('Produk tidak ditemukan.', 404);
        }

        $product->delete();

        return ResponseFormatter::success(
            $product->id,
            'Produk berhasil dihapus.',
            200
        );
    }
}
